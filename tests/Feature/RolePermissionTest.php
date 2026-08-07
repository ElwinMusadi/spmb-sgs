<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Administrator', 'Student', 'Finance', 'Admission Staff'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $this->seed(PermissionSeeder::class);
    }

    private function administrator(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Administrator');

        return $user;
    }

    public function test_default_permissions_are_seeded(): void
    {
        $this->assertTrue(Role::findByName('Administrator')->hasPermissionTo('users.manage'));
        $this->assertTrue(Role::findByName('Finance')->hasPermissionTo('payments.verify'));
        $this->assertFalse(Role::findByName('Finance')->hasPermissionTo('users.manage'));
        $this->assertTrue(Role::findByName('Admission Staff')->hasPermissionTo('documents.verify'));
    }

    public function test_administrator_can_view_roles_page(): void
    {
        $admin = $this->administrator();

        $response = $this->actingAs($admin)->get(route('admin.roles'));

        $response->assertOk();
        $response->assertSee('Roles & Permissions');
    }

    public function test_non_administrator_is_redirected(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $response = $this->actingAs($user)->get(route('admin.roles'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_administrator_can_update_role_permissions(): void
    {
        $admin = $this->administrator();
        $finance = Role::findByName('Finance');
        $permissionId = Permission::findByName('applicants.manage')->id;

        Livewire::actingAs($admin)
            ->test('pages::admin.roles')
            ->set('selectedRoleId', $finance->id)
            ->set("permissionState.{$permissionId}", true)
            ->call('save');

        $this->assertTrue($finance->fresh()->hasPermissionTo('applicants.manage'));
    }

    public function test_administrator_role_permissions_cannot_be_reduced(): void
    {
        $admin = $this->administrator();
        $adminRole = Role::findByName('Administrator');
        $permissionId = Permission::findByName('users.manage')->id;
        $before = $adminRole->permissions()->count();

        Livewire::actingAs($admin)
            ->test('pages::admin.roles')
            ->set('selectedRoleId', $adminRole->id)
            ->set("permissionState.{$permissionId}", false)
            ->call('save');

        $this->assertSame($before, $adminRole->fresh()->permissions()->count());
    }
}
