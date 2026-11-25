<?php
namespace App\Http\Controllers;
use App\Models\{Channel,HotelBooking,HotelRoom};
use App\Services\{HotelBookingService,HotelPropertyService,IndustryModuleService};
use Illuminate\Http\Request;
class PublicHotelBookingController extends Controller
{
    private function access(Channel $channel): void {
        abort_unless($channel->type === 'public' && $channel->status === 'active' && $channel->organization?->isActive() && app(IndustryModuleService::class)->enabled($channel,'bookings'),404);
    }
    public function create(Request $request,Channel $channel,HotelBookingService $service) {
        $this->access($channel);
        $hotel=app(HotelPropertyService::class)->require($channel);
        $data=$request->validate(['check_in'=>'nullable|date_format:Y-m-d|after_or_equal:today','check_out'=>'nullable|required_with:check_in|date_format:Y-m-d|after:check_in','guests'=>'nullable|integer|min:1|max:100']);
        $rooms=collect();$quotes=[];
        if (!empty($data['check_in']) && !empty($data['check_out'])) {
            $dates=$service->dates($data['check_in'],$data['check_out']);
            $rooms=HotelRoom::where('organization_id',$channel->organization_id)->where('hotel_unit_id',$hotel->id)->where('is_active',true)->where('capacity','>=',$data['guests'] ?? 1)->whereDoesntHave('nights',fn($q)=>$q->whereIn('date',$dates))->orderBy('base_rate_minor')->get();
            foreach($rooms as $room) $quotes[$room->id]=array_sum($service->quote($room,$dates));
        }
        return view('hotel.public-create',compact('channel','hotel','rooms','quotes'));
    }
    public function store(Request $request,Channel $channel,HotelBookingService $service) {
        $this->access($channel);
        $data=$request->validate(['submission_key'=>'required|uuid','room_id'=>'required|integer','check_in'=>'required|date_format:Y-m-d|after_or_equal:today','check_out'=>'required|date_format:Y-m-d|after:check_in','guests'=>'required|integer|min:1|max:100','guest_name'=>'required|string|max:160','guest_phone'=>'required|string|max:40','notes'=>'nullable|string|max:2000','marketing_consent'=>'nullable|boolean']);
        $data['guest_email']=$request->user()->email;
        $data['consent_source']='Public hotel reservation form';
        $booking=$service->create($channel,$request->user(),$data);
        return redirect()->route('guest.bookings.show',[$channel,$booking]);
    }
    public function show(Channel $channel,HotelBooking $booking) {
        abort_unless($booking->channel_id === $channel->id && $booking->created_by === auth()->id(),404);
        $booking->load('room');
        return view('hotel.public-show',compact('channel','booking'));
    }
}
