<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\RequiredDocument;
use App\Models\SchoolLevel;
use Illuminate\Database\Seeder;

class AcademicSelectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = [
            'TK' => ['TK A', 'TK B'],
            'SD' => ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
            'SMP' => ['Grade 7', 'Grade 8', 'Grade 9'],
            'SMA' => ['Grade 10', 'Grade 11', 'Grade 12'],
        ];

        foreach ($grades as $levelCode => $names) {
            $level = SchoolLevel::where('code', $levelCode)->first();

            if (! $level) {
                continue;
            }

            foreach ($names as $sort => $name) {
                Grade::firstOrCreate(
                    ['school_level_id' => $level->id, 'code' => str_replace(' ', '-', strtolower($name))],
                    ['name' => $name, 'is_active' => true, 'sort_order' => $sort + 1],
                );
            }
        }

        $documents = [
            ['document_type' => 'akta_kelahiran', 'name' => 'Akta Kelahiran', 'description' => 'Birth certificate', 'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'], 'sort_order' => 1],
            ['document_type' => 'kartu_keluarga', 'name' => 'Kartu Keluarga', 'description' => 'Family card', 'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'], 'sort_order' => 2],
            ['document_type' => 'ijazah', 'name' => 'Ijazah & Transkrip Nilai', 'description' => 'Previous school certificate and transcript', 'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'], 'sort_order' => 3],
            ['document_type' => 'pas_foto', 'name' => 'Pas Foto 4x6', 'description' => 'Recent student photograph', 'allowed_extensions' => ['jpg', 'jpeg', 'png'], 'sort_order' => 4],
        ];

        foreach ($documents as $document) {
            RequiredDocument::firstOrCreate(
                ['document_type' => $document['document_type']],
                [...$document, 'is_required' => true, 'is_active' => true],
            );
        }
    }
}
