<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\SchoolLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_application_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/application');

        $response->assertOk();
        $response->assertSeeLivewire('pages::application.my-application');
    }

    public function test_application_can_be_saved_as_draft(): void
    {
        $user = User::factory()->create();
        $schoolLevel = SchoolLevel::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->set('school_level_id', $schoolLevel->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('student_full_name', 'Test Student')
            ->call('saveAsDraft')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'school_level_id' => $schoolLevel->id,
            'student_full_name' => 'Test Student',
            'status' => 'draft',
            'current_step' => 2,
        ]);
    }

    public function test_application_can_be_saved_and_continued(): void
    {
        $user = User::factory()->create();
        $schoolLevel = SchoolLevel::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->set('school_level_id', $schoolLevel->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('grade_level', 'Grade 1')
            ->set('student_full_name', 'Test Student')
            ->set('student_nik', '1234567890123456')
            ->set('student_citizenship', 'WNI')
            ->set('student_address', 'Jl. Test Address No. 123, RT 01 RW 02')
            ->set('student_birth_place', 'Jakarta')
            ->set('student_birth_date', '2015-01-01')
            ->set('student_gender', 'male')
            ->set('student_religion', 'Islam')
            ->set('father_name', 'Test Father')
            ->set('father_phone', '08123456789')
            ->set('mother_name', 'Test Mother')
            ->set('mother_phone', '08123456789')
            ->call('saveAndContinue')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'school_level_id' => $schoolLevel->id,
            'student_full_name' => 'Test Student',
            'status' => 'draft',
            'current_step' => 3,
        ]);
    }

    public function test_existing_draft_is_loaded_on_mount(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'student_full_name' => 'Existing Student',
        ]);

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->assertSet('student_full_name', 'Existing Student')
            ->assertSet('applicationId', $application->id);
    }

    public function test_documents_can_be_uploaded_in_step_3(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 3,
            'student_nisn' => '1234567890',
        ]);

        $filePdf = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');
        $fileJpg = UploadedFile::fake()->image('photo.jpg', 600, 800)->size(1024);

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->set('doc_akta_kelahiran', $filePdf)
            ->set('doc_kartu_keluarga', $filePdf)
            ->set('doc_ijazah', $filePdf)
            ->set('doc_pas_foto', $fileJpg)
            ->call('saveAndContinue')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'current_step' => 4,
        ]);

        $this->assertDatabaseCount('application_documents', 4);
    }

    public function test_invoice_pdf_can_be_downloaded_by_owner(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 4,
        ]);

        $response = $this->actingAs($user)
            ->get(route('application.invoice.download', $application));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_pdf_cannot_be_downloaded_by_other_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $application = Application::factory()->create([
            'user_id' => $owner->id,
            'current_step' => 4,
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('application.invoice.download', $application));

        $response->assertForbidden();
    }

    public function test_simulate_payment_persists_paid_status(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 4,
        ]);

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->set('payment_method', 'mandiri')
            ->call('simulatePayment')
            ->assertSet('payment_status', 'paid');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'payment_status' => 'paid',
            'payment_method' => 'mandiri',
        ]);
    }
}
