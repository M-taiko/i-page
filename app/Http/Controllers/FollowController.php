<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Organization $organization)
    {
        $user = auth()->user();

        if (!$user->followedOrganizations()->where('organization_id', $organization->id)->exists()) {
            $user->followedOrganizations()->attach($organization->id);
        }

        return back()->with('success', 'Successfully followed ' . $organization->name);
    }

    public function unfollow(Organization $organization)
    {
        auth()->user()->followedOrganizations()->detach($organization->id);

        return back()->with('success', 'Unfollowed ' . $organization->name);
    }

}
