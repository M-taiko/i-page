<?php
namespace App\Services;

use App\Models\{Channel, HotelBooking, HotelPayment, HotelRoom, HotelRoomNight, HotelRoomRate, User};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HotelBookingService
{
    public static function minor(string $amount): int
    {
        if (!preg_match('/^\d{1,7}(\.\d{1,2})?$/', $amount)) { throw ValidationException::withMessages(['amount'=>__('Enter a valid amount with up to two decimal places.')]); }
        [$whole, $fraction] = array_pad(explode('.', $amount), 2, '');
        return (int)$whole * 100 + (int)str_pad($fraction, 2, '0');
    }

    public function dates(string $from, string $until): array
    {
        $start=CarbonImmutable::createFromFormat('!Y-m-d', $from); $end=CarbonImmutable::createFromFormat('!Y-m-d', $until);
        if (!$start || !$end || $start->format('Y-m-d') !== $from || $end->format('Y-m-d') !== $until || $end <= $start || $start->diffInDays($end) > 365) {
            throw ValidationException::withMessages(['check_out'=>__('Choose a stay between 1 and 365 nights.')]);
        }
        $dates=[];
        for ($date=$start; $date < $end; $date=$date->addDay()) { $dates[]=$date->format('Y-m-d'); }
        return $dates;
    }

    public function quote(HotelRoom $room, array $dates): array
    {
        $overrides=$room->rates()->whereIn('date', $dates)->get()->mapWithKeys(fn($rate)=>[$rate->date->format('Y-m-d')=>$rate->rate_minor]);
        $rates=[]; foreach ($dates as $date) { $rates[$date]=(int)($overrides[$date] ?? $room->base_rate_minor); }
        return $rates;
    }

    public function create(Channel $channel, User $actor, array $data): HotelBooking
    {
        abort_unless($channel->status === 'active' && $channel->organization->isActive() && app(IndustryModuleService::class)->enabled($channel,'bookings'),404);
        $property = app(HotelPropertyService::class)->require($channel);
        $dates=$this->dates($data['check_in'], $data['check_out']);
        return DB::transaction(function () use ($channel,$actor,$data,$dates,$property) {
            $room=HotelRoom::where('organization_id',$channel->organization_id)->where('hotel_unit_id',$property->id)->whereKey($data['room_id'])->lockForUpdate()->firstOrFail();
            if (!empty($data['submission_key']) && ($existing=HotelBooking::where('submission_key',$data['submission_key'])->first())) {
                abort_unless($existing->created_by === $actor->id && $existing->channel_id === $channel->id,409);
                return $existing;
            }
            if (!$room->is_active || $data['guests'] > $room->capacity || $room->nights()->whereIn('date',$dates)->exists()) {
                throw ValidationException::withMessages(['room_id'=>__('This room is unavailable for these dates or guest count.')]);
            }
            $rates=$this->quote($room,$dates);
            $booking=HotelBooking::create([
                'reference'=>(string)Str::uuid(),'organization_id'=>$channel->organization_id,'channel_id'=>$channel->id,'room_id'=>$room->id,'created_by'=>$actor->id,
                'submission_key'=>$data['submission_key'] ?? null,
                'guest_name'=>$data['guest_name'],'guest_email'=>$data['guest_email'] ?? null,'guest_phone'=>$data['guest_phone'] ?? null,
                'check_in'=>$data['check_in'],'check_out'=>$data['check_out'],'guests'=>$data['guests'],'status'=>'confirmed',
                'total_minor'=>array_sum($rates),'paid_minor'=>0,'currency'=>$room->currency,'nightly_rates'=>$rates,'notes'=>$data['notes'] ?? null,
                'marketing_consent'=>(bool)($data['marketing_consent'] ?? false),
                'consent_recorded_at'=>!empty($data['marketing_consent']) ? now() : null,
                'consent_source'=>!empty($data['marketing_consent']) ? $data['consent_source'] : null,
            ]);
            foreach ($dates as $date) { HotelRoomNight::create(['room_id'=>$room->id,'booking_id'=>$booking->id,'date'=>$date]); }
            return $booking;
        }, 3);
    }

    public function transition(HotelBooking $booking, string $status): void
    {
        DB::transaction(function () use ($booking,$status) {
            $locked=HotelBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $allowed=['confirmed'=>['checked_in','cancelled'],'checked_in'=>['checked_out'],'checked_out'=>[],'cancelled'=>[]];
            if (!in_array($status,$allowed[$locked->status] ?? [],true)) { throw ValidationException::withMessages(['status'=>__('This status transition is no longer available.')]); }
            if ($status==='checked_in' && (today()->lt($locked->check_in) || today()->gte($locked->check_out))) { throw ValidationException::withMessages(['status'=>__('Check-in is only available during the booked stay.')]); }
            $locked->update(['status'=>$status]);
            if ($status==='cancelled') { HotelRoomNight::where('booking_id',$locked->id)->delete(); }
            if ($status==='checked_out') { HotelRoomNight::where('booking_id',$locked->id)->where('date','>=',today()->toDateString())->delete(); }
        }, 3);
    }

    public function payment(HotelBooking $booking, User $actor, array $data): void
    {
        DB::transaction(function () use ($booking,$actor,$data) {
            $locked=HotelBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $existing=HotelPayment::where('submission_key',$data['submission_key'])->first();
            if ($existing) {
                abort_unless($existing->booking_id === $locked->id && $existing->recorded_by === $actor->id, 409);
                return;
            }
            $amount=self::minor((string)$data['amount']);
            $refund=$data['type']==='refund';
            if ($amount <= 0 || ($refund && $amount > $locked->paid_minor) || (!$refund && ($locked->status==='cancelled' || $amount > $locked->total_minor-$locked->paid_minor))) {
                throw ValidationException::withMessages(['amount'=>__('The amount exceeds the payable or refundable balance.')]);
            }
            $locked->payments()->create([
                'recorded_by'=>$actor->id,'submission_key'=>$data['submission_key'],'type'=>$data['type'],'amount_minor'=>$amount,
                'method'=>$data['method'],'reference'=>$data['reference'] ?? null,'notes'=>$data['notes'] ?? null,'recorded_at'=>now(),
            ]);
            $locked->update(['paid_minor'=>$locked->paid_minor + ($refund ? -$amount : $amount)]);
        }, 3);
    }
}
