<?php
namespace App\Http\Controllers;

use App\Models\{Channel, HotelBooking, HotelRoom, HotelRoomNight, HotelRoomRate};
use App\Services\{HotelBookingService, IndustryModuleService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HotelBookingController extends Controller
{
    private function access(Channel $channel, bool $inventory=false): void
    {
        $user=auth()->user();
        abort_unless($user->currentOrganization?->id === $channel->organization_id && $channel->canAdminister($user),403);
        abort_unless($channel->organization->isActive() && $channel->organization->is_active,403);
        abort_unless(app(IndustryModuleService::class)->enabled($channel,'bookings'),404);
        if ($inventory) { abort_unless(app(\App\Services\HotelPropertyService::class)->require($channel)->channel->canAdminister($user),403); }
    }
    private function booking(Channel $channel, HotelBooking $booking): void
    {
        $this->access($channel);
        abort_unless($booking->organization_id === $channel->organization_id && $booking->channel_id === $channel->id,404);
    }
    private function room(Channel $channel, HotelRoom $room): void
    {
        $this->access($channel,true);
        abort_unless($room->organization_id === $channel->organization_id && $room->hotel_unit_id === app(\App\Services\HotelPropertyService::class)->require($channel)->id,404);
    }
    public function index(Request $request, Channel $channel)
    {
        $this->access($channel);
        $request->validate(['status'=>'nullable|in:confirmed,checked_in,checked_out,cancelled','q'=>'nullable|string|max:160']);
        $query=HotelBooking::where('organization_id',$channel->organization_id)->where('channel_id',$channel->id);
        $summary=['arrivals'=>(clone $query)->whereDate('check_in',today())->where('status','confirmed')->count(),'in_house'=>(clone $query)->where('status','checked_in')->count(),'unpaid'=>(clone $query)->whereIn('status',['confirmed','checked_in','checked_out'])->whereColumn('paid_minor','<','total_minor')->count()];
        $bookings=$query->when($request->filled('status'),fn($q)=>$q->where('status',$request->status))->when($request->filled('q'),fn($q)=>$q->where(fn($s)=>$s->where('guest_name','like','%'.$request->q.'%')->orWhere('guest_email','like','%'.$request->q.'%')->orWhere('reference',$request->q)))->with('room')->latest()->paginate(20)->withQueryString();
        return view('hotel.index',compact('channel','bookings','summary'));
    }
    public function create(Request $request, Channel $channel, HotelBookingService $service)
    {
        $this->access($channel);
        $data=$request->validate(['check_in'=>'nullable|date_format:Y-m-d|after_or_equal:today','check_out'=>'nullable|required_with:check_in|date_format:Y-m-d|after:check_in','guests'=>'nullable|integer|min:1|max:100']);
        $rooms=HotelRoom::where('organization_id',$channel->organization_id)->where('hotel_unit_id',app(\App\Services\HotelPropertyService::class)->require($channel)->id)->where('is_active',true)->where('capacity','>=',$data['guests'] ?? 1);
        $quotes=[];
        if (!empty($data['check_in']) && !empty($data['check_out'])) {
            $dates=$service->dates($data['check_in'],$data['check_out']);
            $rooms->whereDoesntHave('nights',fn($q)=>$q->whereIn('date',$dates));
        }
        $rooms=$rooms->orderBy('number')->get();
        if (isset($dates)) { foreach($rooms as $room) { $quotes[$room->id]=array_sum($service->quote($room,$dates)); } }
        return view('hotel.create',compact('channel','rooms','quotes'));
    }
    public function store(Request $request, Channel $channel, HotelBookingService $service)
    {
        $this->access($channel);
        $data=$request->validate([
            'submission_key'=>'required|uuid','room_id'=>'required|integer','check_in'=>'required|date_format:Y-m-d|after_or_equal:today','check_out'=>'required|date_format:Y-m-d|after:check_in','guests'=>'required|integer|min:1|max:100',
            'guest_name'=>'required|string|max:160','guest_email'=>'nullable|email|max:255','guest_phone'=>'nullable|string|max:40','notes'=>'nullable|string|max:2000',
            'marketing_consent'=>'nullable|boolean','consent_source'=>'nullable|required_if:marketing_consent,1|string|max:255',
        ]);
        $booking=$service->create($channel,$request->user(),$data);
        return redirect()->route('tenant.bookings.show',[$channel,$booking])->with('success',__('Reservation created. No payment was collected.'));
    }
    public function show(Channel $channel, HotelBooking $booking)
    {
        $this->booking($channel,$booking); $booking->load(['room','payments.recorder']);
        return view('hotel.show',compact('channel','booking'));
    }
    public function status(Request $request, Channel $channel, HotelBooking $booking, HotelBookingService $service)
    {
        $this->booking($channel,$booking); $data=$request->validate(['status'=>'required|in:checked_in,checked_out,cancelled']);
        $service->transition($booking,$data['status']); return back()->with('success',__('Reservation status updated.'));
    }
    public function payment(Request $request, Channel $channel, HotelBooking $booking, HotelBookingService $service)
    {
        $this->booking($channel,$booking);
        $data=$request->validate(['submission_key'=>'required|uuid','type'=>'required|in:payment,refund','amount'=>['required','regex:/^\d{1,7}(\.\d{1,2})?$/'],'method'=>'required|in:cash,card,bank,external','reference'=>'nullable|string|max:120','notes'=>'nullable|string|max:1000']);
        $service->payment($booking,$request->user(),$data); return back()->with('success',__('External payment record saved. No money was charged or refunded by the application.'));
    }
    public function rooms(Channel $channel)
    {
        $this->access($channel,true);
        $rooms=HotelRoom::where('organization_id',$channel->organization_id)->where('hotel_unit_id',app(\App\Services\HotelPropertyService::class)->require($channel)->id)->with(['rates'=>fn($q)=>$q->where('date','>=',today())->orderBy('date')->limit(30),'nights'=>fn($q)=>$q->whereNull('booking_id')->where('date','>=',today())->orderBy('date')->limit(30)])->orderBy('number')->paginate(20);
        return view('hotel.rooms',compact('channel','rooms'));
    }
    public function saveRoom(Request $request, Channel $channel, ?HotelRoom $room=null)
    {
        $this->access($channel,true); if ($room) { $this->room($channel,$room); }
        $data=$request->validate(['number'=>['required','string','max:40',Rule::unique('hotel_rooms')->where('hotel_unit_id',app(\App\Services\HotelPropertyService::class)->require($channel)->id)->ignore($room?->id)],'name'=>'required|string|max:120','capacity'=>'required|integer|min:1|max:100','base_rate'=>['required','regex:/^\d{1,7}(\.\d{1,2})?$/'],'currency'=>'required|in:AED,EGP,SAR,USD,EUR,GBP','is_active'=>'nullable|boolean','description'=>'nullable|string|max:2000']);
        $data['base_rate_minor']=HotelBookingService::minor($data['base_rate']); unset($data['base_rate']); $data['is_active']=$request->boolean('is_active');
        DB::transaction(function() use($channel,$room,$data) {
            if ($room) { $locked=HotelRoom::whereKey($room->id)->lockForUpdate()->firstOrFail(); $locked->update($data); }
            else { HotelRoom::create($data+['organization_id'=>$channel->organization_id,'hotel_unit_id'=>app(\App\Services\HotelPropertyService::class)->require($channel)->id]); }
        });
        return back()->with('success',__('Room saved. Existing reservation prices are unchanged.'));
    }
    public function calendar(Request $request, Channel $channel, HotelRoom $room, HotelBookingService $service)
    {
        $this->room($channel,$room);
        $data=$request->validate(['from'=>'required|date_format:Y-m-d|after_or_equal:today','until'=>'required|date_format:Y-m-d|after:from','action'=>'required|in:rate,reset_rate,block,unblock','rate'=>['nullable','required_if:action,rate','regex:/^\d{1,7}(\.\d{1,2})?$/'],'reason'=>'nullable|required_if:action,block|string|max:255']);
        $dates=$service->dates($data['from'],$data['until']);
        DB::transaction(function() use($room,$data,$dates) {
            $locked=HotelRoom::whereKey($room->id)->lockForUpdate()->firstOrFail();
            if ($data['action']==='rate') { foreach($dates as $date) { HotelRoomRate::updateOrCreate(['room_id'=>$locked->id,'date'=>$date],['rate_minor'=>HotelBookingService::minor($data['rate'])]); } }
            if ($data['action']==='reset_rate') { HotelRoomRate::where('room_id',$locked->id)->whereIn('date',$dates)->delete(); }
            if ($data['action']==='block') {
                if ($locked->nights()->whereIn('date',$dates)->whereNotNull('booking_id')->exists()) { throw \Illuminate\Validation\ValidationException::withMessages(['from'=>__('A reservation already occupies part of this period.')]); }
                foreach($dates as $date) { HotelRoomNight::updateOrCreate(['room_id'=>$locked->id,'date'=>$date],['reason'=>$data['reason']]); }
            }
            if ($data['action']==='unblock') { $locked->nights()->whereNull('booking_id')->whereIn('date',$dates)->delete(); }
        },3);
        return back()->with('success',__('Availability and rates updated.'));
    }
}
