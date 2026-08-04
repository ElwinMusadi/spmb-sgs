<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\SchoolLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'school_level_id' => SchoolLevel::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_level' => 'Grade '.fake()->numberBetween(1, 6),
            'status' => 'draft',
            'current_step' => 2,
            'student_full_name' => fake()->name(),
            'student_birth_place' => fake()->city(),
            'student_birth_date' => fake()->dateTimeBetween('-12 years', '-5 years'),
            'student_gender' => fake()->randomElement(['male', 'female']),
            'student_religion' => fake()->randomElement([
                'Islam',
                'Protestant',
                'Catholic',
                'Hindu',
                'Buddhist',
                'Confucian',
            ]),
            'student_previous_school' => fake()->optional()->company(),
            'father_name' => fake()->name('male'),
            'father_phone' => fake()->phoneNumber(),
            'father_email' => fake()->safeEmail(),
            'father_occupation' => fake()->jobTitle(),
            'father_address' => fake()->address(),
            'mother_name' => fake()->name('female'),
            'mother_phone' => fake()->phoneNumber(),
            'mother_email' => fake()->safeEmail(),
            'mother_occupation' => fake()->jobTitle(),
            'mother_address' => fake()->address(),
        ];
    }

    /**
     * Set the application as submitted.
     */
    public function submitted(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'status' => 'submitted',
                'submitted_at' => now(),
            ],
        );
    }
}
