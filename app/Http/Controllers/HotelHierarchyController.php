<?php
namespace App\Http\Controllers;
use App\Models\{Channel, HotelUnit};
use App\Services\HotelHierarchyService;
use Illuminate\Http\Request;
class HotelHierarchyController extends Controller
{
    private function organization() {
        $organization = auth()->user()->currentOrganization;
        abort_unless($organization && $organization->template?->key === 'hotel', 404);
        abort_unless($organization->isActive(), 403);
        return $organization;
    }
    public function index() {
        $organization = $this->organization();
        $manager = Channel::isOrgManager(auth()->user(), $organization->id);
        $units = HotelUnit::where('organization_id',$organization->id)->with('channel','parent')->orderBy('name')->get()
            ->filter(fn ($unit) => $manager || $unit->channel?->membersVisibleTo(auth()->user()));
        $countries = $units->where('kind','country');
        $hotels = $units->where('kind','hotel');
        $parents = $countries->filter(fn ($unit) => $manager || $unit->channel->canAdminister(auth()->user()));
        return view('hotel.hierarchy', compact('organization','manager','countries','hotels','parents'));
    }
    public function store(Request $request, HotelHierarchyService $service) {
        $organization = $this->organization();
        $data = $request->validate(['kind'=>'required|in:hotel','name'=>'required|string|max:120','parent_id'=>'prohibited',
            'country'=>'required|string|max:80','city'=>'required|string|max:120',
            'timezone'=>'required|timezone','currency'=>'required|in:AED,EGP,SAR,USD,EUR,GBP']);
        $service->create($organization,$request->user(),$data);
        return back()->with('success',__('Added to the hotel group. Channel permissions inherit from its parent.'));
    }
}
