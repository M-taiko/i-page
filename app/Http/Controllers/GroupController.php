<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Location;
use App\Repositories\Contracts\GroupRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GroupController extends Controller
{
    protected $groupRepository;

    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function index($organization): View
    {
        $filters = array_merge(request()->all(), ['organization_id' => $organization]);
        $groups = $this->groupRepository->paginate($filters);
        return view('groups.index', compact('groups', 'organization'));
    }

    public function create($organization): View
    {
        $locations = Location::all();
        return view('groups.create-modern', compact('locations', 'organization'));
    }

    public function store(Request $request, $organization): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:groups',
            'description' => 'nullable|string',
            'location_id' => 'required|exists:locations,id',
        ]);

        $validated['organization_id'] = $organization;
        $this->groupRepository->create($validated);

        return redirect()->route('dashboard.groups.index', $organization)
            ->with('success', 'Group created successfully');
    }

    public function show($organization, Group $group): View
    {
        return view('groups.show-modern', compact('group', 'organization'));
    }

    public function edit($organization, Group $group): View
    {
        $locations = Location::all();
        return view('groups.edit-modern', compact('group', 'branches', 'organization'));
    }

    public function update(Request $request, $organization, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:groups,name,' . $group->id,
            'description' => 'nullable|string',
            'location_id' => 'required|exists:locations,id',
        ]);

        $this->groupRepository->update($group, $validated);

        return redirect()->route('dashboard.groups.index', $organization)
            ->with('success', 'Group updated successfully');
    }

    public function destroy($organization, Group $group): RedirectResponse
    {
        $this->groupRepository->delete($group);

        return redirect()->route('dashboard.groups.index', $organization)
            ->with('success', 'Group deleted successfully');
    }
}
