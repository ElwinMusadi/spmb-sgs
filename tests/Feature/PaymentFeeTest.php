<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\PaymentFee;
use App\Models\SchoolLevel;
use App\Models\User;
use App\Services\PaymentFeeService;
use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\PaymentFeeSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SchoolLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentFeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Administrator', 'Student', 'Finance', 'Admission Staff'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $this->seed(PermissionSeeder::class);
        $this->seed(SchoolLevelSeeder::class);
        $this->seed(AcademicYearSeeder::class);
        $this->seed(PaymentFeeSeeder::class);
    }

    private function finance(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Finance');

        return $user;
    }

    public function test_fee_seeder_creates_scoped_and_global_defaults(): void
    {
        $year = AcademicYear::where('is_active', true)->first();
        $level = SchoolLevel::where('is_active', true)->first();

        $this->assertDatabaseHas('payment_fees', [
            'fee_type' => 'registration',
            'academic_year_id' => $year->id,
            'school_level_id' => $level->id,
            'amount' => 500000,
        ]);
        $this->assertDatabaseHas('payment_fees', [
            'fee_type' => 'admin',
            'academic_year_id' => null,
            'school_level_id' => null,
            'amount' => 4500,
        ]);
    }

    public function test_exact_scoped_fees_are_preferred_over_global_fees(): void
    {
        $year = AcademicYear::create(['name' => 'Test Year Exact', 'is_active' => true]);
        $level = SchoolLevel::create(['name' => 'Test Level Exact', 'code' => 'TST', 'is_active' => true]);
        $application = Application::factory()->create([
            'academic_year_id' => $year->id,
            'school_level_id' => $level->id,
        ]);

        PaymentFee::create([
            'fee_type' => 'registration',
            'name' => 'Global Registration',
            'amount' => 100000,
            'is_active' => true,
            'sort_order' => 10,
        ]);
        PaymentFee::create([
            'fee_type' => 'registration',
            'name' => 'Scoped Registration',
            'amount' => 200000,
            'academic_year_id' => $year->id,
            'school_level_id' => $level->id,
            'is_active' => true,
            'sort_order' => 10,
        ]);
        PaymentFee::create([
            'fee_type' => 'admin',
            'name' => 'Global Admin',
            'amount' => 4500,
            'is_active' => true,
            'sort_order' => 90,
        ]);

        $fees = app(PaymentFeeService::class)->resolve($application);

        $this->assertSame(200000, $fees->firstWhere('fee_type', 'registration')->amount);
        $this->assertSame(4500, $fees->firstWhere('fee_type', 'admin')->amount);
    }

    public function test_snapshot_does_not_change_after_fee_edit(): void
    {
        $application = Application::factory()->create([
            'academic_year_id' => AcademicYear::where('is_active', true)->value('id'),
            'school_level_id' => SchoolLevel::where('is_active', true)->value('id'),
        ]);
        $service = app(PaymentFeeService::class);
        $snapshot = $service->ensureSnapshot($application);
        $before = $service->totals($snapshot)['total'];

        PaymentFee::where('fee_type', 'registration')->update(['amount' => 999999]);

        $after = $service->totals($service->feesFor($application))['total'];

        $this->assertSame($before, $after);
    }

    public function test_finance_can_create_scoped_fee(): void
    {
        $finance = $this->finance();
        $year = AcademicYear::create(['name' => 'Test Year CRUD', 'is_active' => true]);
        $level = SchoolLevel::create(['name' => 'Test Level CRUD', 'code' => 'TCR', 'is_active' => true]);

        Livewire::actingAs($finance)
            ->test('pages::admin.finance')
            ->call('createFee')
            ->set('fee_type', 'registration')
            ->set('fee_name', 'Special Registration')
            ->set('fee_description', 'Special fee')
            ->set('fee_amount', '700000')
            ->set('fee_academic_year_id', (string) $year->id)
            ->set('fee_school_level_id', (string) $level->id)
            ->call('saveFee')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_fees', [
            'name' => 'Special Registration',
            'amount' => 700000,
            'academic_year_id' => $year->id,
            'school_level_id' => $level->id,
        ]);
    }

    public function test_finance_cannot_create_scoped_fee_without_scope(): void
    {
        $finance = $this->finance();

        Livewire::actingAs($finance)
            ->test('pages::admin.finance')
            ->call('createFee')
            ->set('fee_type', 'assessment')
            ->set('fee_name', 'Assessment')
            ->set('fee_amount', '100000')
            ->call('saveFee')
            ->assertHasErrors('fee_academic_year_id');
    }
}
