<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $colleagues = $this->colleaguesFor($user);

        return view('settings.profile', compact('user', 'preferences', 'colleagues'));
    }

    /**
     * "Friends" are fully automatic: anyone sharing an active membership in
     * any of the current user's active organizations — no request/accept
     * step, matching the intent of an internal-colleague directory.
     */
    private function colleaguesFor(User $user)
    {
        $activeOrgIds = $user->organizationMemberships()->where('status', 'active')->pluck('organization_id');

        if ($activeOrgIds->isEmpty()) {
            return collect();
        }

        $colleagues = User::whereHas('organizationMemberships', fn ($q) => $q->where('status', 'active')->whereIn('organization_id', $activeOrgIds))
            ->where('id', '!=', $user->id)
            ->with(['organizationMemberships' => fn ($q) => $q->where('status', 'active')->whereIn('organization_id', $activeOrgIds)->with('organization')])
            ->distinct()
            ->get();

        return $colleagues->each(function ($colleague) {
            $colleague->sharedOrganizationNames = $colleague->organizationMemberships
                ->pluck('organization.name')
                ->filter()
                ->unique()
                ->join(', ');
        });
    }

    public function updateProfileOnly(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'mobile' => 'nullable|string|max:24',
            'gender' => 'nullable|in:male,female,other',
            'nationality' => 'nullable|string|max:80',
            'dob' => 'nullable|date|before:today',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'username' => [
                'nullable', 'string', 'max:30', 'regex:/^[a-z0-9_.]+$/i',
                'unique:users,username,' . auth()->id(),
            ],
        ]);

        $user = auth()->user();
        $this->userRepository->update($user, $validated);
        $user->refreshProfileLevel();

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

    /**
     * Self-service account deletion. Business/organization data is never
     * destroyed — if the user is a member of any organization, their
     * membership rows (job_title, employee_id, department, etc.) are kept
     * exactly as-is, just deactivated, so a support/admin action can later
     * re-link that history to a new account if the person signs up again.
     * The account itself is soft-deleted (User already uses SoftDeletes),
     * and its unique fields (email/mobile/username) are anonymized so
     * those values become available again for a fresh registration.
     */
    public function deleteAccount(Request $request): RedirectResponse
    {
        $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'current_password'],
        ]);

        $user = auth()->user();

        $user->organizationMemberships()
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        $user->update([
            'email' => 'deleted+' . $user->id . '+' . time() . '@deleted.ipage.local',
            'mobile' => null,
            'username' => null,
        ]);

        auth()->logout();
        $this->userRepository->delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('guest.home')->with('success', __('Your account has been deleted.'));
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
