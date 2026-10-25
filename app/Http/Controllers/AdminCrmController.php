<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Platform CRM: read-only oversight for the super admin. */
class AdminCrmController extends Controller
{
    public function __construct(private CrmService $crm)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.crm.organizations', [
            'organizations' => $this->crm->organizations($request),
            'summary' => $this->crm->summary(),
        ]);
    }

    public function organization(Organization $organization): View
    {
        return view('admin.crm.organization', $this->crm->organization360($organization));
    }

    public function people(Request $request): View
    {
        return view('admin.crm.people', [
            'people' => $this->crm->people($request),
            'summary' => $this->crm->summary(),
        ]);
    }

    public function person(User $user): View
    {
        return view('admin.crm.person', $this->crm->person360($user));
    }

    public function activity(Request $request): View
    {
        return view('admin.crm.activity', [
            'logs' => $this->crm->activity($request),
            'organizations' => Organization::orderBy('name')->get(['id', 'name']),
            'events' => \App\Models\ActivityLog::query()->distinct()->orderBy('event')->pluck('event'),
        ]);
    }
}
