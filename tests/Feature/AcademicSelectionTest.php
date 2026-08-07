<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\RequiredDocument;
use App\Models\SchoolLevel;
use App\Models\User;
use Database\Seeders\AcademicSelectionSeeder;
use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SchoolLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicSelectionTest extends TestCase
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
        $this->seed(AcademicSelectionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_administrator_and_admission_staff_can_access_academic_selection(): void
    {
        foreach (['Administrator', 'Admission Staff'] as $role) {
            $user = $this->userWithRole($role);

            $this->assertTrue($user->can('academic_selection.manage'));

            $response = $this->actingAs($user)->get(route('admin.academic-selection'));

            $response->assertOk();
            $response->assertSee('Academic Selection');
        }
    }

    public function test_student_cannot_access_academic_selection(): void
    {
        $response = $this->actingAs($this->userWithRole('Student'))->get(route('admin.academic-selection'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_admission_staff_can_create_academic_year_and_level(): void
    {
        $staff = $this->userWithRole('Admission Staff');

        Livewire::actingAs($staff)
            ->test('pages::admin.academic-selection')
            ->call('createYear')
            ->set('yearName', '2027 / 2028')
            ->call('saveYear')
            ->call('createLevel')
            ->set('levelName', 'Sekolah Menengah Kejuruan')
            ->set('levelCode', 'SMK')
            ->set('levelDescription', 'Vocational school')
            ->call('saveLevel')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('academic_years', ['name' => '2027 / 2028']);
        $this->assertDatabaseHas('school_levels', ['code' => 'SMK']);
    }

    public function test_admission_staff_can_create_grade(): void
    {
        $staff = $this->userWithRole('Admission Staff');
        $level = SchoolLevel::first();

        Livewire::actingAs($staff)
            ->test('pages::admin.academic-selection')
            ->call('createGrade')
            ->set('gradeSchoolLevelId', (string) $level->id)
            ->set('gradeName', 'Grade Special')
            ->set('gradeCode', 'SPECIAL')
            ->set('gradeSortOrder', 20)
            ->call('saveGrade')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('grades', ['school_level_id' => $level->id, 'code' => 'SPECIAL']);
    }

    public function test_admission_staff_can_create_required_document_with_extensions(): void
    {
        $staff = $this->userWithRole('Admission Staff');

        Livewire::actingAs($staff)
            ->test('pages::admin.academic-selection')
            ->call('createDocument')
            ->set('documentType', 'report_cards')
            ->set('documentName', 'Report Cards')
            ->set('documentDescription', 'Previous report cards')
            ->set('documentExtensions', 'pdf, jpg, png')
            ->set('documentIsRequired', false)
            ->call('saveDocument')
            ->assertHasNoErrors();

        $document = RequiredDocument::where('document_type', 'report_cards')->firstOrFail();
        $this->assertSame(['pdf', 'jpg', 'png'], $document->allowed_extensions);
        $this->assertFalse($document->is_required);
    }

    public function test_existing_selection_seed_data_is_available(): void
    {
        $this->assertGreaterThan(0, AcademicYear::count());
        $this->assertGreaterThan(0, SchoolLevel::count());
        $this->assertGreaterThan(0, Grade::count());
        $this->assertGreaterThan(0, RequiredDocument::count());
    }
}
