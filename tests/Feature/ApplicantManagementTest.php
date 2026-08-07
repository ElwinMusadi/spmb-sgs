<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicantManagementTest extends TestCase
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

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admission_staff_can_view_applicants(): void
    {
        $staff = $this->userWithRole('Admission Staff');

        $response = $this->actingAs($staff)->get(route('admin.applicants'));

        $response->assertOk();
        $response->assertSee('Applicants');
    }

    public function test_student_cannot_view_applicants(): void
    {
        $student = $this->userWithRole('Student');

        $response = $this->actingAs($student)->get(route('admin.applicants'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_staff_with_review_permission_can_admit_applicant(): void
    {
        $staff = $this->userWithRole('Admission Staff');
        $application = Application::factory()->create(['status' => 'submitted']);

        Livewire::actingAs($staff)
            ->test('pages::admin.applicants')
            ->call('admit', $application->id);

        $this->assertSame('admitted', $application->fresh()->status);
    }

    public function test_finance_cannot_admit_applicant(): void
    {
        $finance = $this->userWithRole('Finance');
        $application = Application::factory()->create(['status' => 'submitted']);

        Livewire::actingAs($finance)
            ->test('pages::admin.applicants')
            ->call('admit', $application->id)
            ->assertStatus(403);

        $this->assertSame('submitted', $application->fresh()->status);
    }

    public function test_finance_can_verify_payment(): void
    {
        $finance = $this->userWithRole('Finance');
        $application = Application::factory()->create(['payment_status' => 'awaiting']);

        Livewire::actingAs($finance)
            ->test('pages::admin.applicants')
            ->call('verifyPayment', $application->id);

        $this->assertSame('paid', $application->fresh()->payment_status);
    }

    public function test_admission_staff_cannot_verify_payment(): void
    {
        $staff = $this->userWithRole('Admission Staff');
        $application = Application::factory()->create(['payment_status' => 'awaiting']);

        Livewire::actingAs($staff)
            ->test('pages::admin.applicants')
            ->call('verifyPayment', $application->id)
            ->assertStatus(403);

        $this->assertSame('awaiting', $application->fresh()->payment_status);
    }

    public function test_status_filter_narrows_results(): void
    {
        $staff = $this->userWithRole('Admission Staff');
        Application::factory()->create(['status' => 'submitted', 'student_full_name' => 'Submitted Kid']);
        Application::factory()->create(['status' => 'draft', 'student_full_name' => 'Draft Kid']);

        Livewire::actingAs($staff)
            ->test('pages::admin.applicants')
            ->set('statusFilter', 'submitted')
            ->assertSee('Submitted Kid')
            ->assertDontSee('Draft Kid');
    }
}
