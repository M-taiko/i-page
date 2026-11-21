<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public const PANEL_SIZE = 8;

    public function index(): View
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /** App-bar bell (docs/27): unread count plus the latest notifications as panel HTML. */
    public function latest(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = $user->notifications()->latest()->limit(self::PANEL_SIZE)->get();

        return response()->json([
            'unread' => $user->notifications()->whereNull('read_at')->count(),
            'html' => view('notifications._panel-items', ['items' => $items])->render(),
        ]);
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse|JsonResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->markAsRead();

        return $request->expectsJson()
            ? response()->json(['unread' => auth()->user()->notifications()->whereNull('read_at')->count()])
            : back();
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        auth()->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return $request->expectsJson()
            ? response()->json(['unread' => 0])
            : back()->with('success', 'All notifications marked as read.');
    }
}
