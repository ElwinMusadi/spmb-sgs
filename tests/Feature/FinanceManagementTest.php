<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\PaymentAccount;
use App\Models\User;
use Database\Seeders\PaymentFeeSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Administrator', 'Student', 'Finance', 'Admission Staff'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $this->seed(PermissionSeeder::class);
        $this->seed(PaymentFeeSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_finance_can_view_finance_page(): void
    {
        $finance = $this->userWithRole('Finance');

        $response = $this->actingAs($finance)->get(route('admin.finance'));

        $response->assertOk();
        $response->assertSee('Finance Management');
    }

    public function test_administrator_can_view_finance_page(): void
    {
        $admin = $this->userWithRole('Administrator');

        $response = $this->actingAs($admin)->get(route('admin.finance'));

        $response->assertOk();
    }

    public function test_admission_staff_cannot_view_finance_page(): void
    {
        $staff = $this->userWithRole('Admission Staff');

        $response = $this->actingAs($staff)->get(route('admin.finance'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_student_cannot_view_finance_page(): void
    {
        $student = $this->userWithRole('Student');

        $response = $this->actingAs($student)->get(route('admin.finance'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_finance_can_verify_payment(): void
    {
        $finance = $this->userWithRole('Finance');
        $application = Application::factory()->create(['payment_status' => 'awaiting']);

        Livewire::actingAs($finance)
            ->test('pages::admin.finance')
            ->call('verifyPayment', $application->id);

        $this->assertSame('paid', $application->fresh()->payment_status);
    }

    public function test_finance_can_add_payment_account(): void
    {
        $finance = $this->userWithRole('Finance');

        Livewire::actingAs($finance)
            ->test('pages::admin.finance')
            ->call('createAccount')
            ->set('bank_name', 'Bank Test')
            ->set('account_number', '111 222 333')
            ->set('account_holder', 'Yayasan Stella Gracia')
            ->set('branch', 'Kupang')
            ->call('saveAccount')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_accounts', [
            'bank_name' => 'Bank Test',
            'account_number' => '111 222 333',
        ]);
    }

    public function test_finance_can_delete_payment_account(): void
    {
        $finance = $this->userWithRole('Finance');
        $account = PaymentAccount::factory()->create();

        Livewire::actingAs($finance)
            ->test('pages::admin.finance')
            ->call('confirmDeleteAccount', $account->id)
            ->call('deleteAccount');

        $this->assertDatabaseMissing('payment_accounts', ['id' => $account->id]);
    }
}
