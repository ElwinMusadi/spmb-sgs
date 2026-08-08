<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\RequiredDocument;
use App\Models\SchoolLevel;
use App\Models\User;
use Database\Seeders\PaymentFeeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Administrator', 'Student', 'Finance', 'Admission Staff'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $this->seed(PaymentFeeSeeder::class);

        foreach (['akta_kelahiran', 'kartu_keluarga', 'ijazah', 'pas_foto'] as $sort => $type) {
            RequiredDocument::create([
                'document_type' => $type,
                'name' => ucwords(str_replace('_', ' ', $type)),
                'allowed_extensions' => $type === 'pas_foto' ? ['jpg', 'jpeg', 'png'] : ['pdf', 'jpg', 'jpeg', 'png'],
                'is_required' => true,
                'is_active' => true,
                'sort_order' => $sort + 1,
            ]);
        }
    }

    /**
     * Create a user with the Student role.
     */
    private function student(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        return $user;
    }

    public function test_my_application_page_is_displayed(): void
    {
        $user = $this->student();

        $response = $this->actingAs($user)->get('/application');

        $response->assertOk();
        $response->assertSeeLivewire('pages::application.my-application');
    }

    public function test_application_can_be_saved_as_draft(): void
    {
        $user = $this->student();
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
        $user = $this->student();
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
        $user = $this->student();
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

        $user = $this->student();
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
        $user = $this->student();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 4,
            'status' => 'admitted',
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
            'status' => 'admitted',
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('application.invoice.download', $application));

        $response->assertForbidden();
    }

    public function test_simulate_payment_persists_paid_status(): void
    {
        $user = $this->student();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 4,
            'status' => 'admitted',
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

    public function test_student_cannot_access_payment_before_admission(): void
    {
        $user = $this->student();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 4,
            'status' => 'submitted',
        ]);

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->assertSet('currentStep', 4)
            ->call('goToStep', 5)
            ->assertSet('currentStep', 4);
    }

    public function test_student_can_submit_after_documents_are_uploaded(): void
    {
        $user = $this->student();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 4,
            'status' => 'draft',
        ]);

        foreach (['akta_kelahiran', 'kartu_keluarga', 'ijazah', 'pas_foto'] as $type) {
            $application->documents()->create([
                'document_type' => $type,
                'file_path' => "documents/{$type}.pdf",
                'file_name' => "{$type}.pdf",
                'file_size' => 100,
            ]);
        }

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->call('submitApplication');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'submitted',
            'current_step' => 4,
        ]);
    }

    public function test_submit_reports_each_missing_document(): void
    {
        $user = $this->student();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 4,
            'status' => 'draft',
        ]);

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->call('submitApplication')
            ->assertHasErrors([
                'doc_akta_kelahiran',
                'doc_kartu_keluarga',
                'doc_ijazah',
                'doc_pas_foto',
            ]);

        $this->assertSame('draft', $application->fresh()->status);
    }

    public function test_temporary_document_can_be_previewed_before_submit(): void
    {
        $user = $this->student();
        Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 4,
            'status' => 'draft',
        ]);
        $file = UploadedFile::fake()->image('birth-certificate.jpg', 100, 100);

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->set('doc_akta_kelahiran', $file)
            ->call('setPreview', 'akta_kelahiran')
            ->assertSet('previewName', 'birth-certificate.jpg')
            ->assertSet('previewType', 'image')
            ->assertSet('showPreviewModal', true)
            ->assertSet('previewUrl', fn (?string $url): bool => str_starts_with($url ?? '', 'data:image/'));
    }

    public function test_user_can_jump_to_a_reached_step_via_stepper(): void
    {
        $user = $this->student();
        Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 5,
            'status' => 'admitted',
        ]);

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->assertSet('currentStep', 5)
            ->assertSet('maxStep', 5)
            ->call('goToStep', 2)
            ->assertSet('currentStep', 2);
    }

    public function test_user_cannot_jump_to_a_step_beyond_reached(): void
    {
        $user = $this->student();
        Application::factory()->create([
            'user_id' => $user->id,
            'current_step' => 2,
        ]);

        Livewire::actingAs($user)
            ->test('pages::application.my-application')
            ->call('goToStep', 4)
            ->assertSet('currentStep', 2);
    }
}
