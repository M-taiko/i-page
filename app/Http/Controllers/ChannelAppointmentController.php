<?php
namespace App\Http\Controllers;
use App\Models\{AppointmentBooking,Channel};
use App\Services\AppointmentBookingService;
use Illuminate\Http\Request;
class ChannelAppointmentController extends Controller
{
    private function access(Channel $channel): void {abort_unless(auth()->user()->currentOrganization?->id===$channel->organization_id&&$channel->canManageBookings(auth()->user()),403);}
    public function index(Channel $channel){$this->access($channel);$bookings=AppointmentBooking::where('channel_id',$channel->id)->with(['service','customer','assignee'])->latest('starts_at')->paginate(20);$canManageServices=app(\App\Services\TenantServiceCatalog::class)->canView(auth()->user(),$channel->organization);return view('appointments.index',compact('channel','bookings','canManageServices'));}
    public function show(Channel $channel,AppointmentBooking $booking){$this->access($channel);abort_unless($booking->channel_id===$channel->id,404);$booking->load(['service','customer','assignee','events.actor']);return view('appointments.show',compact('channel','booking'));}
    public function update(Request $request,Channel $channel,AppointmentBooking $booking,AppointmentBookingService $service){$this->access($channel);abort_unless($booking->channel_id===$channel->id,404);$data=$request->validate(['status'=>'required|in:'.implode(',',AppointmentBookingService::STATUSES),'message'=>'nullable|string|max:2000','customer_visible'=>'nullable|boolean','assigned_to'=>'nullable|integer']);$service->update($booking,$request->user(),$data);return back()->with('success',__('Booking timeline updated.'));}
}
