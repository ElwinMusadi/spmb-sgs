<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationFee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationFee>
 */
class ApplicationFeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'payment_fee_id' => null,
            'fee_type' => 'registration',
            'name' => 'Registration Fee',
            'description' => 'Annual registration fee',
            'amount' => 500000,
            'sort_order' => 10,
        ];
    }
}
