<?php
namespace App\Http\Controllers;
use App\Models\{AppointmentBooking,AppointmentService,Channel};
use App\Services\{AppointmentBookingService,HotelPropertyService,IndustryModuleService};
use Illuminate\Http\Request;
class PublicAppointmentController extends Controller
{
    private function channel(Channel $channel): Channel {abort_unless($channel->type==='public'&&$channel->status==='active'&&app(IndustryModuleService::class)->enabled($channel,'bookings'),404);return $channel;}
    public function create(Request $request, Channel $channel)
    {
        $this->channel($channel);

        $servicesQuery = AppointmentService::query()
            ->where('organization_id', $channel->organization_id)
            ->whereNull('channel_id')
            ->where('is_active', true);

        $selectedServiceId = $request->integer('service') ?: null;
        $focusedService = $selectedServiceId !== null;

        if ($focusedService) {
            $servicesQuery->whereKey($selectedServiceId);
        }

        $services = $servicesQuery->orderBy('name')->get();

        abort_if($focusedService && $services->isEmpty(), 404);

        return view('appointments.public-create', compact('channel', 'services', 'focusedService'));
    }
    public function store(Request $request,Channel $channel,AppointmentBookingService $bookings){$this->channel($channel);$data=$request->validate(['submission_key'=>'required|uuid','service_id'=>'required|integer','date'=>'required|date_format:Y-m-d','time'=>'required|date_format:H:i','guests'=>'required|integer|min:1|max:100','customer_name'=>'required|string|max:160','customer_phone'=>'nullable|string|max:40','customer_notes'=>'nullable|string|max:2000','marketing_consent'=>'nullable|boolean']);$booking=$bookings->create($channel,$request->user(),$data);return redirect()->route('guest.appointments.show',[$channel,$booking])->with('success',__('Booking request sent.')) ;}
    public function show(Channel $channel,AppointmentBooking $booking){abort_unless($booking->channel_id===$channel->id&&$booking->customer_id===auth()->id(),404);$booking->load(['service','events'=>fn($q)=>$q->where('customer_visible',true)->with('actor')->oldest()]);return view('appointments.public-show',compact('channel','booking'));}
}
