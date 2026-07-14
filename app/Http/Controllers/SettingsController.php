<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function showProfile(): View
    {
        $user = auth()->user();
        $preferences = $user->preferences;
        return view('settings.profile', compact('user', 'preferences'));
    }

    public function updateProfileOnly(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'mobile' => 'nullable|string|max:24',
            'gender' => 'nullable|in:male,female,other',
            'nationality' => 'nullable|string|max:80',
        ]);

        $this->userRepository->update(auth()->user(), $validated);

        return redirect()->route('profile.settings')
            ->with('success', 'Profile updated successfully');
    }

    public function updateAppearanceOnly(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => 'nullable|in:light,dark,auto',
            'language' => 'nullable|in:en,ar',
        ]);

        auth()->user()->update($validated);

        return back()->with('success', 'Appearance updated');
    }

    public function updateNotificationsOnly(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
        ]);

        auth()->user()->preferences()->update([
            'email_notifications' => $validated['email_notifications'] ?? false,
            'push_notifications' => $validated['push_notifications'] ?? false,
            'sms_notifications' => $validated['sms_notifications'] ?? false,
        ]);

        return back()->with('success', 'Notification preferences updated');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user = auth()->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);

        return back()->with('success', 'Profile photo updated');
    }

    public function removeAvatar(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return back()->with('success', 'Profile photo removed');
    }

    public function updateCover(Request $request): RedirectResponse
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:6144',
        ]);

        $user = auth()->user();

        if ($user->cover_path) {
            Storage::disk('public')->delete($user->cover_path);
        }

        $path = $request->file('cover')->store('covers', 'public');
        $user->update(['cover_path' => $path]);

        return back()->with('success', 'Cover photo updated');
    }

    public function removeCover(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->cover_path) {
            Storage::disk('public')->delete($user->cover_path);
            $user->update(['cover_path' => null]);
        }

        return back()->with('success', 'Cover photo removed');
    }

    public function show($organization): View
    {
        $user = auth()->user();
        return view('settings.show', compact('user', 'organization'));
    }

    public function updateProfile(Request $request, $organization): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'mobile' => 'nullable|string|max:24',
            'gender' => 'nullable|in:male,female,other',
            'nationality' => 'nullable|string|max:80',
        ]);

        $this->userRepository->update(auth()->user(), $validated);

        return redirect()->route('dashboard.settings.show', $organization)
            ->with('success', 'Profile updated successfully');
    }

    public function updateAppearance(Request $request, $organization): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => 'nullable|in:light,dark,auto',
            'language' => 'nullable|in:en,ar',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('dashboard.settings.show', $organization)
            ->with('success', 'Appearance settings updated');
    }

    public function updateNotifications(Request $request, $organization): RedirectResponse
    {
        $validated = $request->validate([
            'notify_posts' => 'boolean',
            'notify_channels' => 'boolean',
            'notify_mentions' => 'boolean',
            'notify_groups' => 'boolean',
        ]);

        auth()->user()->preferences()->update([
            'notification_posts' => $validated['notify_posts'] ?? false,
            'notification_channels' => $validated['notify_channels'] ?? false,
            'notification_mentions' => $validated['notify_mentions'] ?? false,
            'notification_groups' => $validated['notify_groups'] ?? false,
        ]);

        return redirect()->route('dashboard.settings.show', $organization)
            ->with('success', 'Notification settings updated');
    }
}
