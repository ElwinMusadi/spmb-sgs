<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\SchoolLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('Administrator', 'web');
        $user->assignRole('Administrator');

        return $user;
    }

    public function test_super_admin_is_redirected_from_applicant_dashboard(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_super_admin_can_view_admin_dashboard(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin Overview');
        $response->assertSee('Total Applicants');
    }

    public function test_regular_user_is_redirected_from_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_regular_user_sees_applicant_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Overall Progress');
    }

    public function test_admin_dashboard_reports_application_metrics(): void
    {
        $user = $this->superAdmin();

        $level = SchoolLevel::factory()->create();

        Application::factory()->count(3)->create(['status' => 'draft', 'school_level_id' => $level->id]);
        Application::factory()->count(2)->create(['status' => 'submitted', 'school_level_id' => $level->id]);
        Application::factory()->paid()->create(['status' => 'submitted', 'school_level_id' => $level->id]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Applications by Grade Level');
        $response->assertSee('Status Overview');
    }
}
