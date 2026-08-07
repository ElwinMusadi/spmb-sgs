<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\SchoolLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_level_id' => SchoolLevel::factory(),
            'name' => 'Grade '.fake()->numberBetween(1, 12),
            'code' => 'G'.fake()->unique()->numberBetween(1, 12),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 12),
        ];
    }
}
