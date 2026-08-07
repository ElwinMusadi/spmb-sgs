<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\SchoolLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyApplicantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $schoolLevels = SchoolLevel::where('is_active', true)->get();
        $academicYears = AcademicYear::where('is_active', true)->get();

        if ($schoolLevels->isEmpty() || $academicYears->isEmpty()) {
            $this->command->warn('No active school levels or academic years found. Make sure they are seeded.');
            return;
        }

        for ($i = 0; $i < 14; $i++) {
            // Generate Student Name
            $studentName = $faker->name;
            $email = $faker->unique()->safeEmail;

            // 1. Create User
            $user = User::create([
                'name' => $studentName,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]);

            // Assign Student role
            $user->assignRole('Student');

            // 2. Generate Application Data
            // Randomly determine the progress step (1 to 4)
            // Step 1: Just Registered (No application record yet, or minimal draft)
            // Step 2: Form Filling (Draft)
            // Step 3: Document Upload (Draft)
            // Step 4: Payment (Submitted, awaiting payment or paid)
            
            $step = $faker->numberBetween(1, 4);

            if ($step === 1) {
                // For step 1, they just created an account. In spmb-sgs, application might not be created yet.
                // We just continue to next user.
                continue;
            }

            $schoolLevel = $schoolLevels->random();
            $academicYear = $academicYears->random();
            
            // Basic data for step 2+
            $applicationData = [
                'user_id' => $user->id,
                'school_level_id' => $schoolLevel->id,
                'academic_year_id' => $academicYear->id,
                'grade_level' => 'Grade ' . $faker->numberBetween(1, 6),
                'status' => 'draft',
                'current_step' => $step,
                
                // Student Data
                'student_full_name' => $studentName,
                'student_nik' => $faker->nik(),
                'student_nisn' => $faker->numerify('##########'),
                'student_birth_place' => $faker->city,
                'student_birth_date' => $faker->dateTimeBetween('-12 years', '-6 years')->format('Y-m-d'),
                'student_gender' => $faker->randomElement(['male', 'female']),
                'student_religion' => $faker->randomElement(['Islam', 'Protestant', 'Catholic', 'Hindu', 'Buddhist', 'Confucian']),
                'student_citizenship' => 'WNI',
                'student_address' => $faker->address,
                'student_previous_school' => 'SD ' . $faker->company,

                // Father Data
                'father_name' => $faker->name('male'),
                'father_phone' => $faker->phoneNumber,
                'father_email' => $faker->safeEmail,
                'father_occupation' => $faker->jobTitle,
                'father_address' => $faker->address,

                // Mother Data
                'mother_name' => $faker->name('female'),
                'mother_phone' => $faker->phoneNumber,
                'mother_email' => $faker->safeEmail,
                'mother_occupation' => $faker->jobTitle,
                'mother_address' => $faker->address,
            ];

            // Specific logic for step 4 (payment)
            if ($step === 4) {
                // If they reached step 4, the application is technically submitted and they need to pay.
                $applicationData['status'] = 'submitted';
                $applicationData['submitted_at'] = now();
                
                // Randomize payment status
                $isPaid = $faker->boolean;
                if ($isPaid) {
                    $applicationData['payment_status'] = 'paid';
                    $applicationData['payment_method'] = $faker->randomElement(['bca', 'mandiri', 'bni', 'qris']);
                    $applicationData['paid_at'] = now();
                } else {
                    $applicationData['payment_status'] = 'awaiting';
                }
            }

            Application::create($applicationData);
        }
    }
}
