<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Visitor-facing subscribe / unsubscribe / bell for a channel (docs/27). Returns to the same page,
 * or JSON for the bell button.
 */
class ChannelSubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions)
    {
    }

    public function store(Request $request, Channel $channel): RedirectResponse|JsonResponse
    {
        abort_unless($channel->status === 'active', 404);
        $result = $this->subscriptions->join($request->user(), $channel);

        $message = match ($result) {
            'pending' => __('Your request to join :name is waiting for approval.', ['name' => $channel->name]),
            'already' => __('You are already subscribed to :name.', ['name' => $channel->name]),
            default => __('Subscribed to :name — you\'ll be notified about new posts.', ['name' => $channel->name]),
        };

        return $request->expectsJson()
            ? response()->json(['status' => $result, 'message' => $message])
            : back()->with('success', $message);
    }

    public function destroy(Request $request, Channel $channel): RedirectResponse|JsonResponse
    {
        $this->subscriptions->leave($request->user(), $channel);
        $message = __('Unsubscribed from :name.', ['name' => $channel->name]);

        return $request->expectsJson() ? response()->json(['status' => 'left', 'message' => $message]) : back()->with('success', $message);
    }

    public function bell(Request $request, Channel $channel): RedirectResponse|JsonResponse
    {
        $on = $this->subscriptions->toggleBell($request->user(), $channel);
        abort_if($on === null, 404);

        $message = $on
            ? __('Notifications on for :name.', ['name' => $channel->name])
            : __('Notifications off for :name.', ['name' => $channel->name]);

        return $request->expectsJson() ? response()->json(['on' => $on, 'message' => $message]) : back()->with('success', $message);
    }
}
