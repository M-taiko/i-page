<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index($organization): View
    {
        $filters = array_merge(request()->all(), ['organization_id' => $organization]);
        $users = $this->userRepository->paginate($filters);
        return view('users.index-modern', compact('users', 'organization'));
    }

    public function create($organization): View
    {
        return view('users.create', compact('organization'));
    }

    public function store(Request $request, $organization): RedirectResponse
    {
        $org = \App\Models\Organization::findOrFail($organization);

        $validated = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'email' => 'required|email|max:180|unique:users',
            'mobile' => 'nullable|string|max:24',
            'role' => 'required|in:organization_admin,manager,moderator,staff',
            'password' => 'nullable|string|min:8',
        ]);

        // Create (or find) the invited user.
        $user = \App\Models\User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'mobile' => $validated['mobile'] ?? null,
                'ipage_id' => 'IP' . str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
                'password' => bcrypt($validated['password'] ?? \Illuminate\Support\Str::random(12)),
                'email_verified_at' => now(),
            ]
        );

        // Scoped membership + matching global role (so `can:` permission gates resolve).
        \App\Models\OrganizationMembership::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $user->id],
            ['role' => $validated['role'], 'status' => 'active', 'joined_date' => now(), 'invited_by' => auth()->id()]
        );
        $user->syncRoles([$validated['role']]);

        return redirect()->route('dashboard.users.index', $organization)
            ->with('success', 'Team member added successfully');
    }

    public function show($organization, User $user): View
    {
        return view('users.show', compact('user', 'organization'));
    }

    public function edit($organization, User $user): View
    {
        // Verify user belongs to this organization
        $org = \App\Models\Organization::findOrFail($organization);
        if (!$org->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'User does not belong to this organization');
        }
        return view('users.edit', compact('user', 'organization'));
    }

    public function update(Request $request, $organization, User $user): RedirectResponse
    {
        // Verify user belongs to this organization
        $org = \App\Models\Organization::findOrFail($organization);
        if (!$org->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'User does not belong to this organization');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'email' => 'required|email|max:180|unique:users,email,' . $user->id,
            'mobile' => 'nullable|string|max:24',
            'gender' => 'nullable|in:male,female,other',
            'nationality' => 'nullable|string|max:80',
        ]);

        $this->userRepository->update($user, $validated);

        return redirect()->route('dashboard.users.index', $organization)
            ->with('success', 'User updated successfully');
    }

    public function destroy($organization, User $user): RedirectResponse
    {
        // Verify user belongs to this organization
        $org = \App\Models\Organization::findOrFail($organization);
        if (!$org->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'User does not belong to this organization');
        }

        // Detach user from organization
        $org->users()->detach($user->id);

        return redirect()->route('dashboard.users.index', $organization)
            ->with('success', 'User removed from organization');
    }
}
