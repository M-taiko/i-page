<?php

namespace App\Http\Controllers;

use App\Models\Brand;
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

    public function followBrand(Brand $brand)
    {
        $user = auth()->user();

        if (!$user->followedBrands()->where('brand_id', $brand->id)->exists()) {
            $user->followedBrands()->attach($brand->id);
        }

        return back()->with('success', 'Successfully followed ' . $brand->name);
    }

    public function unfollowBrand(Brand $brand)
    {
        auth()->user()->followedBrands()->detach($brand->id);

        return back()->with('success', 'Unfollowed ' . $brand->name);
    }
}
