# Permission Model

## Purpose

Define role-based access control (RBAC), permission hierarchy, and authorization rules for i-Page. Covers global roles, organization-scoped roles, channel-scoped roles, and the policies that enforce them.

---

## Scope

- Global roles (super_admin, guest)
- Organization-scoped roles (admin, staff, member)
- Channel-scoped roles (admin, moderator, member, viewer)
- Permission definitions per role
- Policy implementation (Policies enforce what each role can do)
- Access control middleware and route protection

---

## Principles

1. **Three Tiers of Authorization**:
   - Global (user is super_admin? → all organizations)
   - Organization (user is admin in this org? → org features)
   - Channel (user is moderator in this channel? → channel moderation)

2. **Principle of Least Privilege**: Users start as guests; roles explicitly granted

3. **Permissions as Claims**: Use spatie/laravel-permission for role→permission mapping

4. **Policies Enforce Rules**: Laravel Policies define *what* a role can do (create, update, delete)

5. **Guests are Limited**: Guests see only public channels, no admin/moderation tools

6. **Soft Roles**: organization_user and channel_user pivots carry role without creating global permissions

---

## Role Hierarchy

### Global Roles (Spatie)

Applied to users globally, stored in `roles` and `model_has_roles` tables.

#### super_admin

- **Scope**: System-wide
- **Assignment**: Manually by system operator (not exposed in UI)
- **Permissions**: All
- **Features**:
  - Manage all organizations (CRUD)
  - Manage admin users
  - Access super-admin dashboard
  - View system analytics (all orgs)
  - Manage roles and permissions
  - System configuration

#### admin (Organization-Scoped via Pivot)

- **Scope**: Single organization
- **Assignment**: By super_admin or organization owner
- **Stored In**: `organization_user.role = 'admin'`
- **Permissions**: Organization admin features
- **Features**:
  - Manage organization settings
  - Manage users in organization
  - Create/edit/delete channels (up to max_channels limit)
  - Manage channel admins
  - View organization analytics
  - Invite users
  - Configure branding (logo, colors)

#### staff (Organization-Scoped via Pivot)

- **Scope**: Single organization
- **Assignment**: By org admin
- **Stored In**: `organization_user.role = 'staff'`
- **Permissions**: Moderate content, manage some channels
- **Features**:
  - Create posts (in assigned channels)
  - Edit own posts
  - Moderate channel comments
  - Access analytics (assigned channels)
  - Invite visitors

#### member (Organization-Scoped via Pivot)

- **Scope**: Single organization
- **Assignment**: By org admin or self-registration via QR
- **Stored In**: `organization_user.role = 'member'`
- **Permissions**: View, post, react
- **Features**:
  - View all public channels
  - View private channels (if invited)
  - Create posts (if permitted)
  - React/comment on posts
  - Update own profile

#### guest

- **Scope**: Global, no organization membership
- **Assignment**: Default for unauthenticated users
- **Stored In**: Global role via spatie (no pivot entry)
- **Permissions**: Minimal
- **Features**:
  - Browse public channels (no auth required)
  - View posts from public channels
  - No creation, editing, or reactioning
  - Can scan QR codes to register

---

### Channel-Scoped Roles (Pivot Table)

Applied per user per channel via `channel_user.role`. Nested under organization.

#### channel_admin

- **Scope**: Single channel within organization
- **Assignment**: By org admin or current channel admin
- **Stored In**: `channel_user.role = 'admin'`
- **Permissions**: Channel moderation
- **Features**:
  - Edit/delete own posts
  - Delete any posts (moderation)
  - Manage channel members (kick, mute, ban)
  - Archive/unarchive channel
  - Edit channel settings

#### channel_moderator

- **Scope**: Single channel
- **Assignment**: By channel admin
- **Stored In**: `channel_user.role = 'moderator'`
- **Permissions**: Light moderation
- **Features**:
  - Edit/delete own posts
  - Delete problematic posts
  - Mute members temporarily
  - Flag posts for escalation

#### channel_member

- **Scope**: Single channel
- **Assignment**: Auto (on organization join) or by channel admin
- **Stored In**: `channel_user.role = 'member'`
- **Permissions**: Standard interaction
- **Features**:
  - View channel posts
  - Create posts (if channel allows)
  - React/comment
  - Edit own posts
  - Upload media

#### channel_viewer

- **Scope**: Single channel
- **Assignment**: By channel admin (read-only access)
- **Stored In**: `channel_user.role = 'viewer'`
- **Permissions**: Read-only
- **Features**:
  - View posts
  - No creation, editing, or moderation

---

## Permission Definitions

Via spatie/laravel-permission, permissions are assigned to roles. Use kebab-case names.

### Super Admin Permissions

```
super_admin role has:
  - admin.view
  - admin.manage-organizations
  - admin.manage-roles
  - admin.view-analytics
  - admin.manage-users
```

### Organization-Level Permissions

```
admin role has:
  - organization.view
  - organization.update
  - organization.manage-settings
  - channel.create
  - channel.update
  - channel.delete
  - user.manage
  - user.invite
  - analytics.view

staff role has:
  - channel.view
  - post.create
  - post.update (own)
  - post.delete (own)
  - analytics.view (assigned)
  - member.moderate

member role has:
  - channel.view
  - post.create (if allowed)
  - post.update (own)
  - post.delete (own)
  - reaction.create

guest role has:
  - (no permissions — check is inverted: "can guest access this?")
```

### Channel-Level Permissions

```
channel_admin has:
  - post.delete (any)
  - post.update (any)
  - member.kick
  - member.mute
  - channel.update
  - channel.archive

channel_moderator has:
  - post.delete (any)
  - member.mute
  - post.flag

channel_member has:
  - post.create
  - post.update (own)
  - post.delete (own)
  - reaction.create

channel_viewer has:
  - (none — read-only)
```

---

## Authorization Flow

### 1. Route Protection

Routes are protected at the middleware level:

```php
// Super Admin Routes
Route::middleware(['auth', 'CheckRole:super_admin'])->prefix('admin')->group(...);

// Organization Routes (any authenticated user belonging to org)
Route::middleware(['auth', 'CheckOrganizationAccess'])->prefix('organization/{id}')->group(...);

// Guest Routes (unauthenticated only)
Route::middleware('guest')->group(...);
```

### 2. Policy Checks

Within controllers, use Laravel Policies to verify fine-grained permissions:

```php
// Check if user can update this post
$this->authorize('update', $post);

// In PostPolicy
public function update(User $user, Post $post): bool
{
    // User must belong to org AND (be post author OR be channel admin)
    return $user->organizations()
        ->where('organization_id', $post->organization_id)
        ->exists() && 
    ($post->author_id === $user->id || 
     $user->can('channel.update', $post->channel));
}
```

### 3. Middleware: CheckRole

```php
// Middleware: CheckRole
// Usage: Route::middleware('CheckRole:super_admin')->group(...)
// Validates:
// - if (!auth()->check()) → redirect to guest.home
// - if (!auth()->user()->hasRole($role)) → abort(403)
```

### 4. Middleware: CheckOrganizationAccess

```php
// Middleware: CheckOrganizationAccess
// Ensures user belongs to {id} organization
// Sets session['current_organization_id']
// Returns 404 if org doesn't exist
// Returns 403 if user not a member
```

---

## Policy Files

### Post Policy

```php
// app/Policies/PostPolicy.php

public function create(User $user, Channel $channel): bool
{
    // User must be a member of the organization
    if (!$user->organizations()->where('organization_id', $channel->organization_id)->exists()) {
        return false;
    }
    
    // Must have post.create permission OR be staff/admin
    return $user->can('post.create', $channel) 
        || $user->hasRole(['admin', 'staff']);
}

public function update(User $user, Post $post): bool
{
    // Author can update own posts
    if ($post->author_id === $user->id) {
        return true;
    }
    
    // Channel admin can update any post
    return $user->hasAnyRole(['admin'])
        && $user->organizations()
            ->where('organization_id', $post->organization_id)
            ->exists();
}

public function delete(User $user, Post $post): bool
{
    // Author can delete own posts
    if ($post->author_id === $user->id) {
        return true;
    }
    
    // Organization admin can delete any post
    return $user->can('post.delete', $post->channel);
}
```

### Channel Policy

```php
// app/Policies/ChannelPolicy.php

public function create(User $user, Organization $org): bool
{
    // Must be org admin
    if (!$user->organizations()
        ->where('organization_id', $org->id)
        ->where('organization_user.role', 'admin')
        ->exists()) {
        return false;
    }
    
    // Check channel limit
    return $org->channels()->count() < $org->max_channels;
}

public function update(User $user, Channel $channel): bool
{
    // Org admin or channel admin
    return $user->hasRole('admin')
        && $user->organizations()
            ->where('organization_id', $channel->organization_id)
            ->exists();
}
```

### User Policy

```php
// app/Policies/UserPolicy.php

public function invite(User $user, Organization $org): bool
{
    // Only org admin can invite
    return $user->organizations()
        ->where('organization_id', $org->id)
        ->where('organization_user.role', 'admin')
        ->exists();
}

public function manage(User $user, Organization $org): bool
{
    // Same as invite
    return $this->invite($user, $org);
}
```

---

## Best Practices

### 1. Default to Deny

```php
// Always default to false in policies
// Explicitly grant permissions

public function view(User $user, Channel $channel): bool
{
    // Does user belong to org?
    if (!$user->organizations()->where('organization_id', $channel->organization_id)->exists()) {
        return false;
    }
    
    // Is channel public?
    if ($channel->type === 'public') {
        return true;
    }
    
    // Is user a member of the channel?
    return $user->channels()->where('channel_id', $channel->id)->exists();
}
```

### 2. Separate Concerns

- **Middleware**: Ensures user is authenticated and belongs to org
- **Policy**: Decides if authenticated user can perform action
- **Permission**: Maps role → action (spatie)

### 3. Cache Permissions

Spatie caches role/permission lookups. Clear on role changes:

```php
auth()->user()->syncRoles(['admin']); // Auto-clears cache
```

### 4. Audit Permissions

Log permission checks for security:

```php
// Optional: add to model observers
if (!$this->authorize('update', $post)) {
    Log::warning("Unauthorized update attempt", [
        'user_id' => auth()->id(),
        'post_id' => $post->id,
    ]);
}
```

---

## Guests and Public Access

### Guest Behavior

**Guests (unauthenticated users)**:

1. Cannot access `/admin/*` → redirected to `/`
2. Cannot access `/organization/{id}/dashboard/*` → redirected to login
3. Can access `/`, `/explore`, `/org/{id}`, `/org/{id}/channel/{slug}` (GuestController routes)
4. Can only see **public** channels
5. Cannot create/edit/delete anything
6. Can scan QR codes and register

### Public Channel Design

```php
// In ChannelController
$publicChannels = Channel::where('type', 'public')
    ->where('organization_id', $orgId)
    ->where('is_archived', false)
    ->get();
    
// Guests see these without auth
// Private channels require membership
```

---

## Implementation Checklist

### On User Creation

1. Assign global `guest` role (via seeders or registration)
2. Create `organization_user` entry when org admin invites
3. Assign `organization_user.role` (admin, staff, member)
4. Auto-create `channel_user` entry for new members (channel_member role)

### On Organization Creation

1. Create `organization_user` entry for owner with `admin` role
2. No global `admin` role (use `organization_user.role = 'admin'` instead)

### On Channel Creation

1. Creator gets `channel_user.role = 'admin'`
2. Channel inherits organization's default permissions

---

## Related Documents

- [[05_DATABASE_ARCHITECTURE.md]] — organization_user, channel_user pivot tables
- [[27_FEATURE_SPECIFICATIONS.md]] — Role requirements per feature
- [[16_SECURITY_GUIDELINES.md]] — Authentication, HTTPS, CSRF

---

# Claude Compliance Checklist

Before implementing any feature, verify:

- [ ] All role tiers defined (global, org, channel)
- [ ] Permissions assigned to roles via spatie
- [ ] Policies implemented per resource (Post, Channel, User, Organization)
- [ ] Route middleware protects authenticated/admin routes
- [ ] Guests cannot access /admin/* or /organization/{id}/dashboard/*
- [ ] Guests can access /explore and public channels
- [ ] Super admin (system operator) properly separated from org admin
- [ ] organization_user.role and channel_user.role used for tenant scoping (not global roles)
- [ ] Authorization checks use policies, not ad-hoc permission checks
- [ ] Audit logging captures permission denials for security review
- [ ] This implementation aligns with 00_PROJECT_CONSTITUTION.md and 40_PRODUCT_GUARDIAN.md
