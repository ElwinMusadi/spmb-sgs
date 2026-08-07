<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\PaymentFee;
use App\Models\SchoolLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentFee>
 */
class PaymentFeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fee_type' => 'registration',
            'name' => 'Registration Fee',
            'description' => 'Annual registration fee',
            'amount' => 500000,
            'academic_year_id' => AcademicYear::factory(),
            'school_level_id' => SchoolLevel::factory(),
            'is_active' => true,
            'sort_order' => 10,
        ];
    }
}
