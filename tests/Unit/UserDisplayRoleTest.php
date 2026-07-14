<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserDisplayRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_role_uses_super_admin_label_for_super_admin_users(): void
    {
        Role::findOrCreate('super_admin', 'web');

        $user = User::factory()->create([
            'first_name' => 'Donia',
            'last_name' => 'Admin',
        ]);
        $user->assignRole('super_admin');

        $this->assertSame('Super Admin', $user->display_role);
    }
}
