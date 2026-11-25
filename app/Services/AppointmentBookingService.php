<?php
namespace App\Services;
use App\Models\{ActivityLog,AppointmentBooking,AppointmentEvent,AppointmentService,Channel,Notification,User};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\{DB};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class AppointmentBookingService
{
    public const STATUSES=['requested','contacted','review','follow_up','confirmed','completed','cancelled'];
    public function create(Channel $channel,User $customer,array $data): AppointmentBooking {
        abort_unless($channel->type==='public' && $channel->status==='active' && app(IndustryModuleService::class)->enabled($channel,'bookings'),404);
        $service=AppointmentService::where('organization_id',$channel->organization_id)->whereNull('channel_id')->where('is_active',true)->findOrFail($data['service_id']);
        $timezone=app(HotelPropertyService::class)->forChannel($channel)?->timezone ?: config('app.timezone');
        $starts=CarbonImmutable::createFromFormat('!Y-m-d H:i',$data['date'].' '.$data['time'],$timezone);
        if(!$starts || $starts->lte(now($timezone)) || $starts->gt(now($timezone)->addYear())) throw ValidationException::withMessages(['date'=>__('Choose a future appointment within one year.')]);
        $ends=$starts->addMinutes($service->duration_minutes);
        return DB::transaction(function() use($channel,$customer,$data,$service,$starts,$ends){
            if($existing=AppointmentBooking::where('submission_key',$data['submission_key'])->first()) {abort_unless($existing->customer_id===$customer->id && $existing->channel_id===$channel->id,409);return $existing;}
            $overlap=AppointmentBooking::where('service_id',$service->id)->whereNotIn('status',['cancelled'])->where('starts_at','<',$ends->utc())->where('ends_at','>',$starts->utc())->lockForUpdate()->sum('guests');
            if($overlap+(int)$data['guests']>$service->slot_capacity) throw ValidationException::withMessages(['time'=>__('This time is no longer available. Choose another appointment.')]);
            $booking=AppointmentBooking::create(['reference'=>(string)Str::uuid(),'submission_key'=>$data['submission_key'],'organization_id'=>$channel->organization_id,'channel_id'=>$channel->id,'service_id'=>$service->id,'customer_id'=>$customer->id,'customer_name'=>$data['customer_name'],'customer_email'=>$customer->email,'customer_phone'=>$data['customer_phone']??null,'starts_at'=>$starts->utc(),'ends_at'=>$ends->utc(),'guests'=>$data['guests'],'status'=>'requested','customer_notes'=>$data['customer_notes']??null,'marketing_consent'=>(bool)($data['marketing_consent']??false),'consent_recorded_at'=>!empty($data['marketing_consent'])?now():null]);
            $this->event($booking,$customer,'created',null,'requested',__('Your booking request was received.'),true);
            $this->notifyManagers($booking,__('New booking request: :service for :customer',['service'=>$service->name,'customer'=>$customer->full_name]));
            return $booking;
        },3);
    }
    public function update(AppointmentBooking $booking,User $actor,array $data): AppointmentEvent {
        abort_unless(in_array($data['status'],self::STATUSES,true),422);
        return DB::transaction(function() use($booking,$actor,$data){
            $locked=AppointmentBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $from=$locked->status;$locked->update(['status'=>$data['status'],'assigned_to'=>$data['assigned_to']??$locked->assigned_to]);
            $visible=(bool)($data['customer_visible']??true);
            $event=$this->event($locked,$actor,'status_changed',$from,$data['status'],$data['message']??null,$visible);
            if($visible) $this->notifyCustomer($locked,__('Booking update: :status',['status'=>str_replace('_',' ',$data['status'])]),$data['message']??null);
            return $event;
        },3);
    }
    private function event(AppointmentBooking $booking,?User $actor,string $event,?string $from,?string $to,?string $message,bool $visible): AppointmentEvent {
        $row=$booking->events()->create(['actor_id'=>$actor?->id,'event'=>$event,'from_status'=>$from,'to_status'=>$to,'message'=>$message,'customer_visible'=>$visible]);
        ActivityLog::create(['user_id'=>$booking->customer_id,'organization_id'=>$booking->organization_id,'subject_type'=>AppointmentBooking::class,'subject_id'=>$booking->id,'event'=>'booking.'.$event,'description'=>$message ?: 'Booking '.$event,'changes'=>['from'=>$from,'to'=>$to,'service_id'=>$booking->service_id,'channel_id'=>$booking->channel_id]]);
        return $row;
    }
    private function notifyManagers(AppointmentBooking $booking,string $message): void {
        $scope=array_merge([$booking->channel_id],$booking->channel->ancestorIds());
        $ids=DB::table('channel_user')->whereIn('channel_id',$scope)->where('status','approved')->where(fn($q)=>$q->where('can_manage_bookings',true)->orWhere('role','admin'))->pluck('user_id');
        $orgManagers=DB::table('organization_memberships')->where('organization_id',$booking->organization_id)->where('status','active')->whereIn('role',['organization_admin','manager'])->pluck('user_id');
        User::whereIn('id',$ids->merge($orgManagers)->unique())->get()->each(fn($user)=>$user->notifications()->create(['type'=>'booking.new','data'=>['message'=>$message,'link'=>route('tenant.appointments.show',[$booking->channel_id,$booking->id])]]));
    }
    private function notifyCustomer(AppointmentBooking $booking,string $message,?string $details=null): void {$booking->customer->notifications()->create(['type'=>'booking.updated','data'=>['message'=>$message.($details?' · '.$details:''),'link'=>route('guest.appointments.show',[$booking->channel_id,$booking->id])]]);}
}
