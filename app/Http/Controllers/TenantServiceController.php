<?php
namespace App\Http\Controllers;
use App\Models\AppointmentService;
use App\Services\TenantServiceCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class TenantServiceController extends Controller
{
    private function organization(Request $request,?string $ability=null){$organization=$request->user()->currentOrganization;$catalog=app(TenantServiceCatalog::class);abort_unless($organization&&($ability?$catalog->can($request->user(),$organization,$ability):$catalog->canView($request->user(),$organization)),403);return $organization;}
    public function index(Request $request){$organization=$this->organization($request);$abilities=app(TenantServiceCatalog::class)->abilities($request->user(),$organization);$services=AppointmentService::where('organization_id',$organization->id)->whereNull('channel_id')->orderBy('name')->get();return view('appointments.services',compact('organization','services','abilities'));}
    public function store(Request $request){$organization=$this->organization($request,'add');$data=$this->validated($request);AppointmentService::create($data+['organization_id'=>$organization->id,'channel_id'=>null,'is_active'=>$request->boolean('is_active')]);return back()->with('success',__('Service added.'));}
    public function update(Request $request,AppointmentService $service){$organization=$this->organization($request,'edit');abort_unless($service->organization_id===$organization->id&&$service->channel_id===null,404);$service->update($this->validated($request)+['is_active'=>$request->boolean('is_active')]);return back()->with('success',__('Service updated.'));}
    public function destroy(Request $request,AppointmentService $service){$organization=$this->organization($request,'delete');abort_unless($service->organization_id===$organization->id&&$service->channel_id===null,404);if($service->bookings()->exists()){return back()->withErrors(['service'=>__('This service has booking history. Disable it instead of deleting it.')]);}$service->delete();return back()->with('success',__('Service deleted.'));}
    private function validated(Request $request): array{return $request->validate(['name'=>'required|string|max:120','kind'=>['required',Rule::in(['room','restaurant','playground','pool','spa','gym','other'])],'description'=>'nullable|string|max:2000','price_from'=>'nullable|numeric|min:0|max:999999999','currency'=>'nullable|required_with:price_from|string|size:3','duration_minutes'=>'required|integer|min:15|max:1440','slot_capacity'=>'required|integer|min:1|max:500','is_active'=>'nullable|boolean']);}
}
