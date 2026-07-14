<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Single source of truth for permissions (global roles; org scoping via organization_memberships).
        $permissions = [
            'channel.create',
            'channel.update',
            'channel.delete',
            'channel.post',
            'brand.manage',
            'user.manage',
            'group.manage',
            'feed.publish',
            'feed.moderate',
            'comment.moderate',
            'ticket.manage',
            'dashboard.view',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ---- Layer 1 ----
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // ---- Layer 2 (organization) ----
        $orgAdmin = Role::firstOrCreate(['name' => 'organization_admin', 'guard_name' => 'web']);
        $orgAdmin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'channel.create', 'channel.update', 'channel.post',
            'feed.publish', 'feed.moderate', 'comment.moderate',
            'ticket.manage', 'user.manage', 'dashboard.view',
        ]);

        $moderator = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $moderator->syncPermissions(['feed.moderate', 'comment.moderate', 'channel.post', 'dashboard.view']);

        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions(['channel.post', 'feed.publish', 'dashboard.view']);

        // ---- Layer 3 (end user) ----
        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $member->syncPermissions([]);
    }
}
