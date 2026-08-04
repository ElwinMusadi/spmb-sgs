<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = [
            ['name' => '2024 / 2025', 'is_active' => false],
            ['name' => '2025 / 2026', 'is_active' => true],
            ['name' => '2026 / 2027', 'is_active' => true],
        ];

        foreach ($years as $year) {
            AcademicYear::updateOrCreate(['name' => $year['name']], $year);
        }
    }
}
