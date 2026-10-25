<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Organization CRM: everything about the current organization's relationships. */
class TenantCrmController extends Controller
{
    public function __construct(private CrmService $crm)
    {
    }

    public function contacts(Request $request): View
    {
        $organization = $this->organization();

        return view('organizations.crm.contacts', [
            'organization' => $organization,
            'contacts' => $this->crm->contacts($organization, $request),
            'summary' => $this->crm->contactSummary($organization),
        ]);
    }

    public function contact(User $user): View
    {
        $organization = $this->organization();
        abort_unless($this->crm->belongsToOrganization($organization, $user), 404);

        return view('organizations.crm.contact', ['organization' => $organization] + $this->crm->contact360($organization, $user));
    }

    public function activity(Request $request): View
    {
        $organization = $this->organization();

        return view('organizations.crm.activity', [
            'organization' => $organization,
            'logs' => $this->crm->activity($request, $organization),
            'events' => \App\Models\ActivityLog::where('organization_id', $organization->id)->distinct()->orderBy('event')->pluck('event'),
        ]);
    }

    private function organization()
    {
        $organization = auth()->user()->currentOrganization;
        abort_unless($organization, 403, 'No organization context');
        \Illuminate\Support\Facades\Gate::authorize('update', $organization);

        return $organization;
    }
}
