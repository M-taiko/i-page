<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\SubscriptionService;

/**
 * Following an organization also subscribes, silently, to its main public channels; unfollowing
 * removes only those (docs/27 "Channel Bell & Organization Follow Subscriptions").
 */
class FollowController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions)
    {
    }

    public function follow(Organization $organization)
    {
        $added = $this->subscriptions->follow(auth()->user(), $organization);

        return back()->with('success', $added
            ? trans_choice('Following :name — you joined :count main channel silently. Turn on its bell to get notifications.|Following :name — you joined :count main channels silently. Turn on their bells to get notifications.', $added, ['name' => $organization->name, 'count' => $added])
            : __('Following :name.', ['name' => $organization->name]));
    }

    public function unfollow(\Illuminate\Http\Request $request, Organization $organization)
    {
        $leaveOwn = $request->boolean('leave_own');
        $this->subscriptions->unfollow($request->user(), $organization, $leaveOwn);

        return back()->with('success', $leaveOwn
            ? __('Unfollowed :name. Its posts are no longer in your feed.', ['name' => $organization->name])
            : __('Unfollowed :name. Channels you joined yourself are kept.', ['name' => $organization->name]));
    }
}
