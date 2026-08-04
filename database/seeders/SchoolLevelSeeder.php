<?php

namespace Database\Seeders;

use App\Models\SchoolLevel;
use Illuminate\Database\Seeder;

class SchoolLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            [
                'name' => 'Taman Kanak-Kanak',
                'code' => 'TK',
                'description' => 'Kindergarten',
            ],
            [
                'name' => 'Sekolah Dasar',
                'code' => 'SD',
                'description' => 'Primary',
            ],
            [
                'name' => 'Sekolah Menengah Pertama',
                'code' => 'SMP',
                'description' => 'Middle School',
            ],
            [
                'name' => 'Sekolah Menengah Atas',
                'code' => 'SMA',
                'description' => 'High School',
            ],
        ];

        foreach ($levels as $level) {
            SchoolLevel::updateOrCreate(['code' => $level['code']], $level);
        }
    }
}
