<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Administrator', 'Student', 'Finance', 'Admission Staff'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function administrator(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Administrator');

        return $user;
    }

    public function test_administrator_can_view_user_management(): void
    {
        $admin = $this->administrator();

        $response = $this->actingAs($admin)->get(route('admin.users'));

        $response->assertOk();
        $response->assertSee('User Management');
    }

    public function test_non_administrator_is_redirected(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $response = $this->actingAs($user)->get(route('admin.users'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_administrator_can_create_a_user_with_a_role(): void
    {
        $admin = $this->administrator();

        Livewire::actingAs($admin)
            ->test('pages::admin.users')
            ->call('createUser')
            ->set('name', 'Budi Santoso')
            ->set('email', 'b.santoso@stellagracia.com')
            ->set('password', 'secret123')
            ->set('role', 'Admission Staff')
            ->set('is_active', true)
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'email' => 'b.santoso@stellagracia.com',
            'is_active' => true,
        ]);

        $user = User::where('email', 'b.santoso@stellagracia.com')->first();
        $this->assertTrue($user->hasRole('Admission Staff'));
    }

    public function test_administrator_can_update_a_user_role(): void
    {
        $admin = $this->administrator();
        $target = User::factory()->create();
        $target->assignRole('Student');

        Livewire::actingAs($admin)
            ->test('pages::admin.users')
            ->call('editUser', $target->id)
            ->set('role', 'Finance')
            ->call('saveUser')
            ->assertHasNoErrors();

        $target->refresh();
        $this->assertTrue($target->hasRole('Finance'));
        $this->assertFalse($target->hasRole('Student'));
    }

    public function test_administrator_cannot_delete_own_account(): void
    {
        $admin = $this->administrator();

        Livewire::actingAs($admin)
            ->test('pages::admin.users')
            ->call('confirmDelete', $admin->id)
            ->call('deleteUser');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_administrator_can_delete_another_user(): void
    {
        $admin = $this->administrator();
        $target = User::factory()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.users')
            ->call('confirmDelete', $target->id)
            ->call('deleteUser');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_search_filters_users_by_name(): void
    {
        $admin = $this->administrator();
        User::factory()->create(['name' => 'Zebra Unique']);
        User::factory()->create(['name' => 'Other Person']);

        Livewire::actingAs($admin)
            ->test('pages::admin.users')
            ->set('search', 'Zebra')
            ->assertSee('Zebra Unique')
            ->assertDontSee('Other Person');
    }
}
