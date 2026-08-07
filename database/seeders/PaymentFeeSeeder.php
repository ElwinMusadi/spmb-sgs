<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\PaymentFee;
use App\Models\SchoolLevel;
use App\Services\PaymentFeeService;
use Illuminate\Database\Seeder;

class PaymentFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = AcademicYear::where('is_active', true)->get();
        $levels = SchoolLevel::where('is_active', true)->get();

        foreach ($years as $year) {
            foreach ($levels as $level) {
                PaymentFee::firstOrCreate(
                    ['fee_type' => 'registration', 'academic_year_id' => $year->id, 'school_level_id' => $level->id],
                    ['name' => 'Registration Fee', 'description' => 'Annual registration fee', 'amount' => 500000, 'is_active' => true, 'sort_order' => 10],
                );
                PaymentFee::firstOrCreate(
                    ['fee_type' => 'assessment', 'academic_year_id' => $year->id, 'school_level_id' => $level->id],
                    ['name' => 'Entrance Assessment Test', 'description' => 'Computer-based cognitive assessment', 'amount' => 250000, 'is_active' => true, 'sort_order' => 20],
                );
            }
        }

        PaymentFee::firstOrCreate(
            ['fee_type' => 'registration', 'academic_year_id' => null, 'school_level_id' => null],
            ['name' => 'Registration Fee', 'description' => 'Annual registration fee', 'amount' => 500000, 'is_active' => true, 'sort_order' => 10],
        );
        PaymentFee::firstOrCreate(
            ['fee_type' => 'assessment', 'academic_year_id' => null, 'school_level_id' => null],
            ['name' => 'Entrance Assessment Test', 'description' => 'Computer-based cognitive assessment', 'amount' => 250000, 'is_active' => true, 'sort_order' => 20],
        );

        PaymentFee::firstOrCreate(
            ['fee_type' => 'admin', 'academic_year_id' => null, 'school_level_id' => null],
            ['name' => 'Admin Fee (Virtual Account)', 'description' => 'Virtual account administration fee', 'amount' => 4500, 'is_active' => true, 'sort_order' => 90],
        );

        $feeService = app(PaymentFeeService::class);

        Application::query()
            ->where('current_step', '>=', 4)
            ->with(['fees', 'academicYear', 'schoolLevel'])
            ->each(function (Application $application) use ($feeService): void {
                if (! $application->hasFeeSnapshot()) {
                    $feeService->ensureSnapshot($application);
                }
            });
    }
}
