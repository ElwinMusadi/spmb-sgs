<?php

use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\SchoolLevel;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('My Application')] class extends Component {
    use WithFileUploads;

    // Wizard State
    public int $currentStep = 2;

    // Furthest step the user has reached (controls stepper navigation).
    public int $maxStep = 2;

    // Academic Selection
    public ?int $school_level_id = null;
    public ?int $academic_year_id = null;
    public ?string $grade_level = null;

    // Student Data
    public ?string $student_full_name = null;
    public ?string $student_nik = null;
    public ?string $student_nisn = null;
    public ?string $student_birth_place = null;
    public ?string $student_birth_date = null;
    public ?string $student_gender = null;
    public ?string $student_religion = null;
    public ?string $student_citizenship = null;
    public ?string $student_address = null;
    public ?string $student_previous_school = null;

    // Parent Data - Father
    public ?string $father_name = null;
    public ?string $father_phone = null;
    public ?string $father_email = null;
    public ?string $father_occupation = null;
    public ?string $father_address = null;

    // Parent Data - Mother
    public ?string $mother_name = null;
    public ?string $mother_phone = null;
    public ?string $mother_email = null;
    public ?string $mother_occupation = null;
    public ?string $mother_address = null;

    // Parent Data - Guardian
    public ?string $guardian_name = null;
    public ?string $guardian_phone = null;
    public ?string $guardian_email = null;
    public ?string $guardian_occupation = null;
    public ?string $guardian_address = null;

    // Document Uploads
    public $doc_akta_kelahiran;
    public $doc_kartu_keluarga;
    public $doc_ijazah;
    public $doc_pas_foto;

    public bool $has_akta_kelahiran = false;
    public bool $has_kartu_keluarga = false;
    public bool $has_ijazah = false;
    public bool $has_pas_foto = false;

    // UI State
    public string $activeParentTab = 'father';

    // Document Preview & Stored Data
    public array $stored_docs = [];
    public ?string $previewUrl = null;
    public ?string $previewName = null;
    public ?string $previewType = null;
    public bool $showPreviewModal = false;

    // Payment State
    public string $payment_method = 'bca';
    public string $payment_status = 'awaiting';
    // Internal
    public ?int $applicationId = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $application = Auth::user()->applications()->latest()->first();

        if ($application) {
            $this->applicationId = $application->id;
            $this->currentStep = max(2, $application->current_step);
            $this->maxStep = $this->currentStep;
            $this->payment_status = $application->payment_status;
            $this->payment_method = $application->payment_method ?? 'bca';
            $this->fillFromApplication($application);
            $this->loadExistingDocuments($application);
        }
    }

    /**
     * Fill form fields from an existing application.
     */
    private function fillFromApplication(Application $application): void
    {
        $this->school_level_id = $application->school_level_id;
        $this->academic_year_id = $application->academic_year_id;
        $this->grade_level = $application->grade_level;

        $this->student_full_name = $application->student_full_name;
        $this->student_nik = $application->student_nik;
        $this->student_nisn = $application->student_nisn;
        $this->student_birth_place = $application->student_birth_place;
        $this->student_birth_date = $application->student_birth_date?->format('Y-m-d');
        $this->student_gender = $application->student_gender;
        $this->student_religion = $application->student_religion;
        $this->student_citizenship = $application->student_citizenship;
        $this->student_address = $application->student_address;
        $this->student_previous_school = $application->student_previous_school;

        $this->father_name = $application->father_name;
        $this->father_phone = $application->father_phone;
        $this->father_email = $application->father_email;
        $this->father_occupation = $application->father_occupation;
        $this->father_address = $application->father_address;

        $this->mother_name = $application->mother_name;
        $this->mother_phone = $application->mother_phone;
        $this->mother_email = $application->mother_email;
        $this->mother_occupation = $application->mother_occupation;
        $this->mother_address = $application->mother_address;

        $this->guardian_name = $application->guardian_name;
        $this->guardian_phone = $application->guardian_phone;
        $this->guardian_email = $application->guardian_email;
        $this->guardian_occupation = $application->guardian_occupation;
        $this->guardian_address = $application->guardian_address;
    }

    /**
     * Load existing documents state.
     */
    private function loadExistingDocuments(Application $application): void
    {
        $docs = $application->documents()->get();
        $docTypes = $docs->pluck('document_type')->toArray();
        $this->has_akta_kelahiran = in_array('akta_kelahiran', $docTypes);
        $this->has_kartu_keluarga = in_array('kartu_keluarga', $docTypes);
        $this->has_ijazah = in_array('ijazah', $docTypes);
        $this->has_pas_foto = in_array('pas_foto', $docTypes);

        $this->stored_docs = [];
        foreach ($docs as $doc) {
            $this->stored_docs[$doc->document_type] = [
                'name' => $doc->file_name,
                'size' => $doc->file_size,
                'path' => $doc->file_path,
                'url' => Storage::url($doc->file_path),
            ];
        }
    }

    /**
     * Set the file to be previewed in the modal.
     */
    public function setPreview(string $type): void
    {
        if (isset($this->stored_docs[$type])) {
            $this->previewUrl = $this->stored_docs[$type]['url'];
            $this->previewName = $this->stored_docs[$type]['name'];
        } elseif ($this->{"doc_$type"}) {
            // Temporary uploaded file
            $file = $this->{"doc_$type"};
            try {
                $this->previewUrl = $file->temporaryUrl();
                $this->previewName = $file->getClientOriginalName();
            } catch (\Exception $e) {
                Flux::toast(variant: 'danger', text: __('Preview not available for this file type until saved.'));
                return;
            }
        } else {
            return;
        }

        $extension = strtolower(pathinfo($this->previewName, PATHINFO_EXTENSION));
        $this->previewType = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf';
        $this->showPreviewModal = true;
    }

    /**
     * Remove a document.
     */
    public function removeDocument(string $type): void
    {
        // Check if we have a temporary uploaded file
        if ($this->{"doc_$type"}) {
            $this->{"doc_$type"} = null;
        }

        // Check if it's already in the database
        if ($this->applicationId && isset($this->stored_docs[$type])) {
            $application = Auth::user()->applications()->find($this->applicationId);
            if ($application) {
                $doc = $application->documents()->where('document_type', $type)->first();
                if ($doc) {
                    Storage::disk('public')->delete($doc->file_path);
                    $doc->delete();
                }
            }
            unset($this->stored_docs[$type]);
            $this->{"has_$type"} = false;
        }
    }
    /**
     * Simulate Payment.
     */
    public function simulatePayment(): void
    {
        // Simulate a delay
        sleep(1);
        $this->payment_status = 'paid';

        if ($this->applicationId) {
            $application = Auth::user()->applications()->findOrFail($this->applicationId);
            $application->update([
                'payment_status' => 'paid',
                'payment_method' => $this->payment_method,
                'paid_at' => now(),
            ]);
        }
    }

    /**
     * Get all form data as an array.
     *
     * @return array<string, mixed>
     */
    private function getFormData(): array
    {
        return [
            'school_level_id' => $this->school_level_id,
            'academic_year_id' => $this->academic_year_id,
            'grade_level' => $this->grade_level,
            'student_full_name' => $this->student_full_name,
            'student_nik' => $this->student_nik,
            'student_nisn' => $this->student_nisn,
            'student_birth_place' => $this->student_birth_place,
            'student_birth_date' => $this->student_birth_date ?: null,
            'student_gender' => $this->student_gender,
            'student_religion' => $this->student_religion,
            'student_citizenship' => $this->student_citizenship,
            'student_address' => $this->student_address,
            'student_previous_school' => $this->student_previous_school,
            'father_name' => $this->father_name,
            'father_phone' => $this->father_phone,
            'father_email' => $this->father_email,
            'father_occupation' => $this->father_occupation,
            'father_address' => $this->father_address,
            'mother_name' => $this->mother_name,
            'mother_phone' => $this->mother_phone,
            'mother_email' => $this->mother_email,
            'mother_occupation' => $this->mother_occupation,
            'mother_address' => $this->mother_address,
            'guardian_name' => $this->guardian_name,
            'guardian_phone' => $this->guardian_phone,
            'guardian_email' => $this->guardian_email,
            'guardian_occupation' => $this->guardian_occupation,
            'guardian_address' => $this->guardian_address,
        ];
    }

    /**
     * Save the application data to database.
     */
    private function saveApplicationData(int $targetStep): void
    {
        $data = array_merge($this->getFormData(), [
            'status' => 'draft',
            'current_step' => $targetStep,
        ]);

        if ($this->applicationId) {
            $application = Auth::user()->applications()->findOrFail($this->applicationId);
            $application->update($data);
        } else {
            $application = Auth::user()->applications()->create($data);
            $this->applicationId = $application->id;
        }
    }

    /**
     * Save the application as a draft.
     */
    public function saveAsDraft(): void
    {
        $this->saveApplicationData($this->currentStep);
        Flux::toast(variant: 'success', text: __('Application saved as draft.'));
    }

    /**
     * Go to the previous step.
     */
    public function previousStep(): void
    {
        if ($this->currentStep > 2) {
            $this->currentStep--;
        }
    }

    /**
     * Jump directly to a step the user has already reached.
     */
    public function goToStep(int $step): void
    {
        if ($step >= 2 && $step <= $this->maxStep) {
            $this->currentStep = $step;
        }
    }

    /**
     * Save and continue to the next step.
     */
    public function saveAndContinue(): void
    {
        if ($this->currentStep === 2) {
            $this->validate([
                'school_level_id' => ['required', 'exists:school_levels,id'],
                'academic_year_id' => ['required', 'exists:academic_years,id'],
                'grade_level' => ['required', 'string', 'max:50'],
                'student_full_name' => ['required', 'string', 'max:255'],
                'student_nik' => ['required', 'string', 'size:16'],
                'student_nisn' => ['nullable', 'string', 'size:10'],
                'student_birth_place' => ['required', 'string', 'max:255'],
                'student_birth_date' => ['required', 'date', 'before:today'],
                'student_gender' => ['required', 'in:male,female'],
                'student_religion' => ['required', 'in:Islam,Protestant,Catholic,Hindu,Buddhist,Confucian'],
                'student_citizenship' => ['required', 'string', 'max:100'],
                'student_address' => ['required', 'string', 'max:1000'],
                'father_name' => ['required', 'string', 'max:255'],
                'father_phone' => ['required', 'string', 'max:20'],
                'mother_name' => ['required', 'string', 'max:255'],
                'mother_phone' => ['required', 'string', 'max:20'],
            ]);

            $this->saveApplicationData(3);
            $this->currentStep = 3;
            $this->maxStep = max($this->maxStep, 3);
            Flux::toast(variant: 'success', text: __('Form saved! Please upload required documents.'));
        } elseif ($this->currentStep === 3) {
            $this->validate(
                [
                    'doc_akta_kelahiran' => [$this->has_akta_kelahiran ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                    'doc_kartu_keluarga' => [$this->has_kartu_keluarga ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                    'doc_ijazah' => [$this->has_ijazah ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                    'doc_pas_foto' => [$this->has_pas_foto ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
                ],
                [
                    'doc_pas_foto.mimes' => __('Pas Foto must be an image (JPEG, JPG, PNG).'),
                ],
            );

            $application = Auth::user()->applications()->findOrFail($this->applicationId);
            $year = date('Y');
            $identifier = $this->student_nisn ?: $this->applicationId;
            $path = "documents/{$year}/{$identifier}";

            $docsToUpload = [
                'akta_kelahiran' => $this->doc_akta_kelahiran,
                'kartu_keluarga' => $this->doc_kartu_keluarga,
                'ijazah' => $this->doc_ijazah,
                'pas_foto' => $this->doc_pas_foto,
            ];

            foreach ($docsToUpload as $type => $file) {
                if ($file) {
                    $filename = $file->getClientOriginalName();
                    $storedPath = $file->storeAs($path, uniqid() . '_' . $filename, 'public');

                    $application->documents()->updateOrCreate(
                        ['document_type' => $type],
                        [
                            'file_path' => $storedPath,
                            'file_name' => $filename,
                            'file_size' => $file->getSize(),
                        ],
                    );
                }
            }

            $this->loadExistingDocuments($application);
            $this->saveApplicationData(4);
            $this->currentStep = 4;
            $this->maxStep = max($this->maxStep, 4);
            Flux::toast(variant: 'success', text: __('Documents confirmed! Please proceed to payment.'));
        }
    }

    /**
     * Set the active parent/guardian tab.
     */
    public function setParentTab(string $tab): void
    {
        $this->activeParentTab = $tab;
    }

    /**
     * Get the available school levels.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, SchoolLevel>
     */
    #[Computed]
    public function schoolLevels()
    {
        return SchoolLevel::where('is_active', true)->get();
    }

    /**
     * Get the available academic years.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, AcademicYear>
     */
    #[Computed]
    public function academicYears()
    {
        return AcademicYear::where('is_active', true)->get();
    }

    /**
     * Get the current application instance.
     */
    #[Computed]
    public function application(): ?Application
    {
        if (!$this->applicationId) {
            return null;
        }

        return Auth::user()->applications()->find($this->applicationId);
    }
};
?>

<div class="flex flex-col w-full max-w-7xl mx-auto gap-8 pb-16">

  {{-- Page Header --}}
  <div class="flex flex-col gap-2">
    <div class="flex items-center gap-3">
      <span class="material-symbols-outlined text-navy text-[32px]">edit_document</span>
      <h1 class="font-heading text-3xl font-semibold text-navy">{{ __('My Application') }}</h1>
    </div>
    <p class="font-body text-lg text-on-surface-variant max-w-3xl">
      {{ __('Complete your application details below. You can save your progress and return at any time before the final submission deadline.') }}
    </p>
  </div>

  {{-- Application Progress Card --}}
  <div class="bg-white rounded-xl shadow-sm p-8 border border-outline-variant/30">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div class="flex flex-col gap-1">
        <h2 class="font-heading text-xl font-semibold text-on-surface">{{ __('Application Progress') }}</h2>
        <p class="text-sm text-on-surface-variant">
          @if ($currentStep === 2)
            {{ __('Please complete all required fields in the Student and Parent data sections.') }}
          @elseif ($currentStep === 3)
            {{ __('Please upload all required documents to proceed.') }}
          @elseif ($currentStep === 4)
            {{ __('Please complete the registration payment.') }}
          @endif
        </p>
      </div>
      <div class="px-3 py-1.5 bg-gold-pale/30 rounded-full flex items-center gap-1.5 self-start md:self-center">
        <div class="w-2 h-2 rounded-full bg-gold animate-pulse"></div>
        <span class="font-body text-xs font-semibold text-gold-dark uppercase tracking-wider">
          @if ($currentStep === 2)
            {{ __('Form Filling') }}
          @elseif ($currentStep === 3)
            {{ __('Document Upload') }}
          @elseif ($currentStep === 4)
            {{ __('Payment') }}
          @endif
        </span>
      </div>
    </div>

    {{-- Progress Stepper --}}
    <div class="relative pt-6 pb-2 px-2 md:px-6">
      {{-- Background Line --}}
      <div
        class="absolute top-11.5 left-[12%] right-[12%] md:left-[10%] md:right-[10%] h-1.5 bg-slate-200 rounded-full z-0">
      </div>

      {{-- Active Line --}}
      <div
        class="absolute top-11.5 left-[12%] md:left-[10%] h-1.5 bg-navy rounded-full z-0 transition-all duration-700 ease-in-out shadow-[0_0_10px_rgba(25,55,109,0.3)]"
        style="width: {{ $currentStep === 2 ? '30%' : ($currentStep === 3 ? '60%' : '80%') }};"></div>

      <div class="flex justify-between relative z-10">
        {{-- Step 1: Account (Always completed) --}}
        <div class="flex flex-col items-center gap-2.5 w-20 md:w-24 group cursor-default">
          <div
            class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-navy flex items-center justify-center text-white shadow-md ring-4 ring-white transition-transform group-hover:scale-105">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">check</span>
          </div>
          <span
            class="font-body text-[11px] md:text-xs font-bold text-navy text-center uppercase tracking-wider">{{ __('Account') }}</span>
        </div>

        {{-- Step 2: Form --}}
        <button type="button" wire:click="goToStep(2)" @disabled($maxStep < 2)
          class="flex flex-col items-center gap-2.5 w-20 md:w-24 group {{ $maxStep >= 2 ? 'cursor-pointer' : 'cursor-default' }}">
          @if ($currentStep > 2)
            <div
              class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-navy flex items-center justify-center text-white shadow-md ring-4 ring-white transition-transform group-hover:scale-105">
              <span class="material-symbols-outlined text-[20px]"
                style="font-variation-settings: 'FILL' 1;">check</span>
            </div>
            <span
              class="font-body text-[11px] md:text-xs font-bold text-navy text-center uppercase tracking-wider">{{ __('Form') }}</span>
          @elseif ($currentStep === 2)
            <div
              class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-white border-[3px] border-navy flex items-center justify-center text-navy shadow-[0_0_15px_rgba(25,55,109,0.2)] ring-4 ring-white transition-transform scale-110">
              <span class="font-body text-base font-bold">2</span>
            </div>
            <span
              class="font-body text-[11px] md:text-xs font-bold text-navy text-center uppercase tracking-wider">{{ __('Form') }}</span>
          @else
            <div
              class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant border-2 border-white ring-4 ring-white transition-colors">
              <span class="font-body text-base font-semibold">2</span>
            </div>
            <span
              class="font-body text-[11px] md:text-xs font-medium text-on-surface-variant text-center uppercase tracking-wider">{{ __('Form') }}</span>
          @endif
        </button>

        {{-- Step 3: Documents --}}
        <button type="button" wire:click="goToStep(3)" @disabled($maxStep < 3)
          class="flex flex-col items-center gap-2.5 w-20 md:w-24 group {{ $maxStep >= 3 ? 'cursor-pointer' : 'cursor-default' }}">
          @if ($currentStep > 3)
            <div
              class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-navy flex items-center justify-center text-white shadow-md ring-4 ring-white transition-transform group-hover:scale-105">
              <span class="material-symbols-outlined text-[20px]"
                style="font-variation-settings: 'FILL' 1;">check</span>
            </div>
            <span
              class="font-body text-[11px] md:text-xs font-bold text-navy text-center uppercase tracking-wider">{{ __('Documents') }}</span>
          @elseif ($currentStep === 3)
            <div
              class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-white border-[3px] border-navy flex items-center justify-center text-navy shadow-[0_0_15px_rgba(25,55,109,0.2)] ring-4 ring-white transition-transform scale-110">
              <span class="font-body text-base font-bold">3</span>
            </div>
            <span
              class="font-body text-[11px] md:text-xs font-bold text-navy text-center uppercase tracking-wider">{{ __('Documents') }}</span>
          @else
            <div
              class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant border-2 border-white ring-4 ring-white transition-colors">
              <span class="font-body text-base font-semibold">3</span>
            </div>
            <span
              class="font-body text-[11px] md:text-xs font-medium text-on-surface-variant text-center uppercase tracking-wider">{{ __('Documents') }}</span>
          @endif
        </button>

        {{-- Step 4: Payment --}}
        <button type="button" wire:click="goToStep(4)" @disabled($maxStep < 4)
          class="flex flex-col items-center gap-2.5 w-20 md:w-24 group {{ $maxStep >= 4 ? 'cursor-pointer' : 'cursor-default' }}">
          @if ($currentStep > 4)
            <div
              class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-navy flex items-center justify-center text-white shadow-md ring-4 ring-white transition-transform group-hover:scale-105">
              <span class="material-symbols-outlined text-[20px]"
                style="font-variation-settings: 'FILL' 1;">check</span>
            </div>
            <span
              class="font-body text-[11px] md:text-xs font-bold text-navy text-center uppercase tracking-wider">{{ __('Payment') }}</span>
          @elseif ($currentStep === 4)
            <div
              class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-white border-[3px] border-navy flex items-center justify-center text-navy shadow-[0_0_15px_rgba(25,55,109,0.2)] ring-4 ring-white transition-transform scale-110">
              <span class="font-body text-base font-bold">4</span>
            </div>
            <span
              class="font-body text-[11px] md:text-xs font-bold text-navy text-center uppercase tracking-wider">{{ __('Payment') }}</span>
          @else
            <div
              class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant border-2 border-white ring-4 ring-white transition-colors">
              <span class="font-body text-base font-semibold">4</span>
            </div>
            <span
              class="font-body text-[11px] md:text-xs font-medium text-on-surface-variant text-center uppercase tracking-wider">{{ __('Payment') }}</span>
          @endif
        </button>
      </div>
    </div>
  </div>

  {{-- Main Content Grid --}}
  <div class="grid grid-cols-12 gap-8">

    {{-- Left Column: Main Content --}}
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-8">

      @if ($currentStep === 2)
        {{-- STEP 2: FORM CONTENT --}}

        {{-- Academic Selection Card --}}
        <div class="bg-white rounded-xl shadow-sm p-8 border border-outline-variant/30">
          <h3 class="font-heading text-lg font-semibold text-navy border-b border-surface-container-high pb-3 mb-6">
            {{ __('Academic Selection') }}</h3>

          {{-- School Level Radio Buttons --}}
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @foreach ($this->schoolLevels as $level)
              <label class="cursor-pointer group">
                <input wire:model="school_level_id" type="radio" name="school_level" value="{{ $level->id }}"
                  class="peer sr-only" />
                <div
                  class="px-4 py-3 rounded-lg border border-outline-variant text-center transition-all
                  peer-checked:bg-navy peer-checked:border-navy peer-checked:text-white
                  group-hover:border-navy hover:bg-surface-container">
                  <span class="font-body text-sm font-semibold block">{{ $level->code }}</span>
                  <span class="font-body text-xs opacity-70 block mt-1">{{ $level->description }}</span>
                </div>
              </label>
            @endforeach
          </div>
          @error('school_level_id')
            <p class="text-red-600 text-sm mb-4">{{ $message }}</p>
          @enderror

          {{-- Academic Year & Grade Level --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <flux:select wire:model="grade_level" :label="__('Grade Level')" :placeholder="__('Select Grade')">
                <flux:select.option value="Grade 1">Grade 1</flux:select.option>
                <flux:select.option value="Grade 2">Grade 2</flux:select.option>
                <flux:select.option value="Grade 3">Grade 3</flux:select.option>
                <flux:select.option value="Grade 4">Grade 4</flux:select.option>
                <flux:select.option value="Grade 5">Grade 5</flux:select.option>
                <flux:select.option value="Grade 6">Grade 6</flux:select.option>
              </flux:select>
            </div>
            <div class="flex flex-col gap-1.5">
              <flux:select wire:model="academic_year_id" :label="__('Academic Year')" :placeholder="__('Select Year')">
                @foreach ($this->academicYears as $year)
                  <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
                @endforeach
              </flux:select>
            </div>
          </div>
        </div>

        {{-- Student Data Card --}}
        <div class="bg-white rounded-xl shadow-sm p-8 border border-outline-variant/30">
          <h3
            class="font-heading text-lg font-semibold text-navy border-b border-surface-container-high pb-3 mb-6 flex justify-between items-center">
            {{ __('Student Data') }}
            <span
              class="text-xs font-body font-semibold text-red-600 bg-red-50 px-3 py-1 rounded-full">{{ __('Required') }}</span>
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 gap-y-5">
            <div class="md:col-span-2">
              <flux:input wire:model="student_full_name" :label="__('Full Name (as per Birth Certificate)')"
                type="text" :placeholder="__('Enter full name')" />
            </div>

            <flux:input wire:model="student_nik" :label="__('NIK (Nomor Induk Kependudukan)')" type="text"
              maxlength="16" :placeholder="__('16-digit NIK')" />
            <flux:input wire:model="student_nisn" :label="__('NISN (Optional)')" type="text" maxlength="10"
              :placeholder="__('10-digit NISN')" />

            <flux:input wire:model="student_birth_place" :label="__('Place of Birth')" type="text"
              :placeholder="__('City')" />
            <flux:input wire:model="student_birth_date" :label="__('Date of Birth')" type="date" />

            <flux:radio.group wire:model="student_gender" :label="__('Gender')" class="flex items-start gap-6 pt-2">
              <flux:radio value="male" :label="__('Male')" />
              <flux:radio value="female" :label="__('Female')" />
            </flux:radio.group>

            <flux:select wire:model="student_religion" :label="__('Religion')" :placeholder="__('Select Religion')">
              <flux:select.option value="Islam">Islam</flux:select.option>
              <flux:select.option value="Protestant">Protestant</flux:select.option>
              <flux:select.option value="Catholic">Catholic</flux:select.option>
              <flux:select.option value="Hindu">Hindu</flux:select.option>
              <flux:select.option value="Buddhist">Buddhist</flux:select.option>
              <flux:select.option value="Confucian">Confucian</flux:select.option>
            </flux:select>

            <flux:input wire:model="student_citizenship" :label="__('Citizenship')" type="text"
              :placeholder="__('e.g. WNI or WNA')" />

            <div class="md:col-span-2">
              <flux:textarea wire:model="student_address" :label="__('Complete Address')" rows="3"
                :placeholder="__('Street, RT/RW, Village, District, City, Province, Postal Code')" />
            </div>

            <div class="md:col-span-2">
              <flux:input wire:model="student_previous_school" :label="__('Previous School (if applicable)')"
                type="text" :placeholder="__('Name of previous school')" />
            </div>
          </div>
        </div>

        {{-- Parent / Guardian Data Card --}}
        <div class="bg-white rounded-xl shadow-sm p-8 border border-outline-variant/30">
          <h3
            class="font-heading text-lg font-semibold text-navy border-b border-surface-container-high pb-3 mb-6 flex justify-between items-center">
            {{ __('Parent / Guardian Data') }}
            <span
              class="text-xs font-body font-semibold text-red-600 bg-red-50 px-3 py-1 rounded-full">{{ __('Required') }}</span>
          </h3>

          <div class="space-y-6">
            {{-- Tab Navigation --}}
            <div class="flex gap-4 border-b border-surface-container-high pb-0">
              <button wire:click.prevent="setParentTab('father')" type="button"
                class="font-body text-sm font-semibold pb-2.5 px-2 transition-colors border-b-2
                {{ $activeParentTab === 'father' ? 'text-navy border-navy' : 'text-on-surface-variant border-transparent hover:text-navy' }}">
                {{ __('Father') }}
              </button>
              <button wire:click.prevent="setParentTab('mother')" type="button"
                class="font-body text-sm font-semibold pb-2.5 px-2 transition-colors border-b-2
                {{ $activeParentTab === 'mother' ? 'text-navy border-navy' : 'text-on-surface-variant border-transparent hover:text-navy' }}">
                {{ __('Mother') }}
              </button>
              <button wire:click.prevent="setParentTab('guardian')" type="button"
                class="font-body text-sm font-semibold pb-2.5 px-2 transition-colors border-b-2
                {{ $activeParentTab === 'guardian' ? 'text-navy border-navy' : 'text-on-surface-variant border-transparent hover:text-navy' }}">
                {{ __('Guardian') }}
              </button>
            </div>

            {{-- Father Tab --}}
            @if ($activeParentTab === 'father')
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 gap-y-5 pt-2">
                <div class="md:col-span-2">
                  <flux:input wire:model="father_name" :label="__('Full Name')" type="text"
                    :placeholder="__('Enter full name')" />
                </div>
                <flux:input wire:model="father_phone" :label="__('Mobile Number')" type="tel"
                  placeholder="+62" />
                <flux:input wire:model="father_email" :label="__('Email Address')" type="email"
                  placeholder="example@email.com" />
                <div class="md:col-span-2">
                  <flux:input wire:model="father_occupation" :label="__('Occupation')" type="text"
                    :placeholder="__('Current occupation')" />
                </div>
                <div class="md:col-span-2">
                  <flux:textarea wire:model="father_address" :label="__('Home Address')"
                    :placeholder="__('Full residential address')" rows="3" />
                </div>
              </div>
            @endif

            {{-- Mother Tab --}}
            @if ($activeParentTab === 'mother')
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 gap-y-5 pt-2">
                <div class="md:col-span-2">
                  <flux:input wire:model="mother_name" :label="__('Full Name')" type="text"
                    :placeholder="__('Enter full name')" />
                </div>
                <flux:input wire:model="mother_phone" :label="__('Mobile Number')" type="tel"
                  placeholder="+62" />
                <flux:input wire:model="mother_email" :label="__('Email Address')" type="email"
                  placeholder="example@email.com" />
                <div class="md:col-span-2">
                  <flux:input wire:model="mother_occupation" :label="__('Occupation')" type="text"
                    :placeholder="__('Current occupation')" />
                </div>
                <div class="md:col-span-2">
                  <flux:textarea wire:model="mother_address" :label="__('Home Address')"
                    :placeholder="__('Full residential address')" rows="3" />
                </div>
              </div>
            @endif

            {{-- Guardian Tab --}}
            @if ($activeParentTab === 'guardian')
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 gap-y-5 pt-2">
                <div class="md:col-span-2">
                  <flux:input wire:model="guardian_name" :label="__('Full Name')" type="text"
                    :placeholder="__('Enter full name')" />
                </div>
                <flux:input wire:model="guardian_phone" :label="__('Mobile Number')" type="tel"
                  placeholder="+62" />
                <flux:input wire:model="guardian_email" :label="__('Email Address')" type="email"
                  placeholder="example@email.com" />
                <div class="md:col-span-2">
                  <flux:input wire:model="guardian_occupation" :label="__('Occupation')" type="text"
                    :placeholder="__('Current occupation')" />
                </div>
                <div class="md:col-span-2">
                  <flux:textarea wire:model="guardian_address" :label="__('Home Address')"
                    :placeholder="__('Full residential address')" rows="3" />
                </div>
              </div>
            @endif
          </div>
        </div>
      @elseif ($currentStep === 3)
        {{-- STEP 3: DOCUMENTS CONTENT --}}
        <div class="bg-white rounded-xl shadow-sm p-8 border border-outline-variant/30">
          <div class="mb-6">
            <h3 class="font-heading text-lg font-semibold text-navy">{{ __('Upload Required Documents') }}</h3>
            <p class="text-sm text-on-surface-variant">
              {{ __('Please upload all the required documents below. Maximum file size is 5MB per file.') }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Akta Kelahiran --}}
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant/50">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-navy/10 text-navy flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined">description</span>
                </div>
                <div>
                  <h4 class="font-semibold text-on-surface text-sm">{{ __('Akta Kelahiran') }}</h4>
                  <p class="text-xs text-on-surface-variant">{{ __('PDF, JPG, PNG') }}</p>
                  @if ($has_akta_kelahiran)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 mt-1">
                      <span class="material-symbols-outlined text-[14px]">check_circle</span>
                      {{ __('Document uploaded') }}
                    </span>
                  @endif
                </div>
              </div>

              @if ($has_akta_kelahiran || $doc_akta_kelahiran)
                <div class="mt-4 p-3 bg-surface rounded-lg flex flex-col gap-3 border border-outline-variant/30">
                  <div class="flex items-center gap-2 overflow-hidden">
                    <span class="material-symbols-outlined text-navy text-[20px] shrink-0">draft</span>
                    <span class="text-sm font-medium text-on-surface truncate">
                      {{ $has_akta_kelahiran ? $stored_docs['akta_kelahiran']['name'] ?? 'Document' : $doc_akta_kelahiran->getClientOriginalName() }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" wire:click.prevent="setPreview('akta_kelahiran')"
                      class="flex-1 flex items-center justify-center gap-1 py-1.5 px-2 rounded-md text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>
                      <span>{{ __('Preview') }}</span>
                    </button>
                    <button type="button" wire:click.prevent="removeDocument('akta_kelahiran')"
                      class="flex-1 flex items-center justify-center gap-1 py-1.5 px-2 rounded-md text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">delete</span>
                      <span>{{ __('Remove') }}</span>
                    </button>
                  </div>
                </div>
              @else
                <flux:input type="file" wire:model="doc_akta_kelahiran" accept=".pdf,.jpg,.jpeg,.png" />
                <div wire:loading wire:target="doc_akta_kelahiran"
                  class="text-sm text-navy mt-1 font-semibold flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>
                  {{ __('Uploading...') }}
                </div>
                @error('doc_akta_kelahiran')
                  <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
              @endif
            </div>

            {{-- Kartu Keluarga --}}
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant/50">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-navy/10 text-navy flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined">group</span>
                </div>
                <div>
                  <h4 class="font-semibold text-on-surface text-sm">{{ __('Kartu Keluarga') }}</h4>
                  <p class="text-xs text-on-surface-variant">{{ __('PDF, JPG, PNG') }}</p>
                  @if ($has_kartu_keluarga)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 mt-1">
                      <span class="material-symbols-outlined text-[14px]">check_circle</span>
                      {{ __('Document uploaded') }}
                    </span>
                  @endif
                </div>
              </div>

              @if ($has_kartu_keluarga || $doc_kartu_keluarga)
                <div class="mt-4 p-3 bg-surface rounded-lg flex flex-col gap-3 border border-outline-variant/30">
                  <div class="flex items-center gap-2 overflow-hidden">
                    <span class="material-symbols-outlined text-navy text-[20px] shrink-0">draft</span>
                    <span class="text-sm font-medium text-on-surface truncate">
                      {{ $has_kartu_keluarga ? $stored_docs['kartu_keluarga']['name'] ?? 'Document' : $doc_kartu_keluarga->getClientOriginalName() }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" wire:click.prevent="setPreview('kartu_keluarga')"
                      class="flex-1 flex items-center justify-center gap-1 py-1.5 px-2 rounded-md text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>
                      <span>{{ __('Preview') }}</span>
                    </button>
                    <button type="button" wire:click.prevent="removeDocument('kartu_keluarga')"
                      class="flex-1 flex items-center justify-center gap-1 py-1.5 px-2 rounded-md text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">delete</span>
                      <span>{{ __('Remove') }}</span>
                    </button>
                  </div>
                </div>
              @else
                <flux:input type="file" wire:model="doc_kartu_keluarga" accept=".pdf,.jpg,.jpeg,.png" />
                <div wire:loading wire:target="doc_kartu_keluarga"
                  class="text-sm text-navy mt-1 font-semibold flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>
                  {{ __('Uploading...') }}
                </div>
                @error('doc_kartu_keluarga')
                  <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
              @endif
            </div>

            {{-- Ijazah --}}
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant/50">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-navy/10 text-navy flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined">school</span>
                </div>
                <div>
                  <h4 class="font-semibold text-on-surface text-sm">{{ __('Ijazah & Transkrip Nilai') }}</h4>
                  <p class="text-xs text-on-surface-variant">{{ __('PDF, JPG, PNG') }}</p>
                  @if ($has_ijazah)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 mt-1">
                      <span class="material-symbols-outlined text-[14px]">check_circle</span>
                      {{ __('Document uploaded') }}
                    </span>
                  @endif
                </div>
              </div>

              @if ($has_ijazah || $doc_ijazah)
                <div class="mt-4 p-3 bg-surface rounded-lg flex flex-col gap-3 border border-outline-variant/30">
                  <div class="flex items-center gap-2 overflow-hidden">
                    <span class="material-symbols-outlined text-navy text-[20px] shrink-0">draft</span>
                    <span class="text-sm font-medium text-on-surface truncate">
                      {{ $has_ijazah ? $stored_docs['ijazah']['name'] ?? 'Document' : $doc_ijazah->getClientOriginalName() }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" wire:click.prevent="setPreview('ijazah')"
                      class="flex-1 flex items-center justify-center gap-1 py-1.5 px-2 rounded-md text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>
                      <span>{{ __('Preview') }}</span>
                    </button>
                    <button type="button" wire:click.prevent="removeDocument('ijazah')"
                      class="flex-1 flex items-center justify-center gap-1 py-1.5 px-2 rounded-md text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">delete</span>
                      <span>{{ __('Remove') }}</span>
                    </button>
                  </div>
                </div>
              @else
                <flux:input type="file" wire:model="doc_ijazah" accept=".pdf,.jpg,.jpeg,.png" />
                <div wire:loading wire:target="doc_ijazah"
                  class="text-sm text-navy mt-1 font-semibold flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>
                  {{ __('Uploading...') }}
                </div>
                @error('doc_ijazah')
                  <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
              @endif
            </div>

            {{-- Pas Foto --}}
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant/50">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-navy/10 text-navy flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined">portrait</span>
                </div>
                <div>
                  <h4 class="font-semibold text-on-surface text-sm">{{ __('Pas Foto 4x6') }}</h4>
                  <p class="text-xs text-on-surface-variant">{{ __('JPG, PNG Only') }}</p>
                  @if ($has_pas_foto)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 mt-1">
                      <span class="material-symbols-outlined text-[14px]">check_circle</span>
                      {{ __('Document uploaded') }}
                    </span>
                  @endif
                </div>
              </div>

              @if ($has_pas_foto || $doc_pas_foto)
                <div class="mt-4 p-3 bg-surface rounded-lg flex flex-col gap-3 border border-outline-variant/30">
                  <div class="flex items-center gap-2 overflow-hidden">
                    <span class="material-symbols-outlined text-navy text-[20px] shrink-0">draft</span>
                    <span class="text-sm font-medium text-on-surface truncate">
                      {{ $has_pas_foto ? $stored_docs['pas_foto']['name'] ?? 'Document' : $doc_pas_foto->getClientOriginalName() }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" wire:click.prevent="setPreview('pas_foto')"
                      class="flex-1 flex items-center justify-center gap-1 py-1.5 px-2 rounded-md text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>
                      <span>{{ __('Preview') }}</span>
                    </button>
                    <button type="button" wire:click.prevent="removeDocument('pas_foto')"
                      class="flex-1 flex items-center justify-center gap-1 py-1.5 px-2 rounded-md text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">delete</span>
                      <span>{{ __('Remove') }}</span>
                    </button>
                  </div>
                </div>
              @else
                <flux:input type="file" wire:model="doc_pas_foto" accept=".jpg,.jpeg,.png" />
                <div wire:loading wire:target="doc_pas_foto"
                  class="text-sm text-navy mt-1 font-semibold flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>
                  {{ __('Uploading...') }}
                </div>
                @error('doc_pas_foto')
                  <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
              @endif
            </div>
          </div>

          {{-- Document Preview Modal --}}
          <flux:modal wire:model="showPreviewModal" class="min-w-[50vw]">
            <div class="flex justify-between items-center mb-4">
              <flux:heading size="lg">{{ __('Document Preview') }}</flux:heading>
              @if ($previewName)
                <span class="text-sm text-on-surface-variant max-w-[200px] truncate"
                  title="{{ $previewName }}">{{ $previewName }}</span>
              @endif
            </div>

            <div
              class="bg-surface-container-lowest rounded-xl overflow-hidden border border-outline-variant/30 flex items-center justify-center min-h-[400px]">
              @if ($previewUrl)
                @if ($previewType === 'image')
                  <img src="{{ $previewUrl }}" alt="Preview" class="max-w-full max-h-[70vh] object-contain" />
                @else
                  <iframe src="{{ $previewUrl }}" class="w-full h-[70vh] border-0"></iframe>
                @endif
              @else
                <div class="flex flex-col items-center justify-center text-on-surface-variant p-12">
                  <span class="material-symbols-outlined text-[48px] mb-2 opacity-50">broken_image</span>
                  <p>{{ __('Preview not available') }}</p>
                </div>
              @endif
            </div>
          </flux:modal>
        </div>
      @elseif ($currentStep === 4)
        {{-- STEP 4: PAYMENT CONTENT --}}

        <div class="flex flex-col gap-6">
          <header
            class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-surface-container-high pb-6">
            <div class="flex flex-col gap-1">
              <h1 class="font-heading text-2xl font-semibold text-on-surface">{{ __('Payment & Invoices') }}</h1>
              <p class="font-body text-base text-on-surface-variant">
                {{ __('Review and manage your registration fees.') }}</p>
            </div>
            <div class="flex items-center gap-3">
              @if ($applicationId)
                <a href="{{ route('application.invoice.download', $applicationId) }}"
                  class="px-4 py-2 flex items-center gap-2 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors font-body text-sm font-semibold">
                  <span class="material-symbols-outlined text-[20px]">download</span>
                  {{ __('Download Invoice (PDF)') }}
                </a>
              @endif
            </div>
          </header>

          <div class="flex flex-col gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
              <div class="p-6 border-b border-outline-variant/30 flex justify-between items-start">
                <div class="flex flex-col gap-1">
                  <span
                    class="font-body text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __('Invoice No.') }}</span>
                  <span
                    class="font-heading text-xl font-semibold text-navy">INV-SGS-{{ date('Y') }}-{{ str_pad($applicationId ?? 0, 4, '0', STR_PAD_LEFT) }}</span>
                  <span class="font-body text-sm text-on-surface-variant mt-2">{{ __('Issued:') }}
                    {{ now()->format('M d, Y') }} &bull; {{ __('Due:') }}
                    {{ now()->addDays(7)->format('M d, Y') }}</span>
                </div>
                @if ($payment_status === 'paid')
                  <div
                    class="px-3 py-1.5 bg-green-100 text-green-800 rounded-full font-body text-sm font-semibold flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    {{ __('Lunas / Paid') }}
                  </div>
                @else
                  <div
                    class="px-3 py-1.5 bg-red-100 text-red-800 rounded-full font-body text-sm font-semibold flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">pending_actions</span>
                    {{ __('Awaiting Payment') }}
                  </div>
                @endif
              </div>
              <div class="p-6">
                {{-- Line Items --}}
                <div class="overflow-hidden rounded-xl border border-outline-variant/40">
                  <table class="w-full text-left border-collapse">
                    <thead>
                      <tr class="bg-surface-container-lowest">
                        <th
                          class="py-3 px-4 font-body text-xs font-semibold text-on-surface-variant uppercase tracking-wider">
                          {{ __('Description') }}</th>
                        <th
                          class="py-3 px-4 font-body text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-right whitespace-nowrap">
                          {{ __('Amount (IDR)') }}</th>
                      </tr>
                    </thead>
                    <tbody class="font-body text-base text-on-surface divide-y divide-outline-variant/20">
                      <tr>
                        <td class="py-4 px-4">
                          <div class="flex items-center gap-3">
                            <span
                              class="material-symbols-outlined text-navy text-[22px] shrink-0">app_registration</span>
                            <div>
                              <div class="font-medium leading-tight">
                                {{ __('Registration Fee - Academic Year') }}
                                {{ date('Y') }}/{{ date('Y') + 1 }}</div>
                              <div class="text-xs text-on-surface-variant mt-0.5">{{ __('Grade') }}
                                {{ $grade_level ?? '-' }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="py-4 px-4 text-right font-medium tabular-nums whitespace-nowrap">500.000</td>
                      </tr>
                      <tr>
                        <td class="py-4 px-4">
                          <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-navy text-[22px] shrink-0">quiz</span>
                            <div>
                              <div class="font-medium leading-tight">{{ __('Entrance Assessment Test') }}</div>
                              <div class="text-xs text-on-surface-variant mt-0.5">
                                {{ __('Computer-based cognitive assessment') }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="py-4 px-4 text-right font-medium tabular-nums whitespace-nowrap">250.000</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                {{-- Totals Summary --}}
                <div class="mt-6 flex justify-end">
                  <div class="w-full md:w-2/3 lg:w-1/2 flex flex-col gap-2.5">
                    <div class="flex items-center justify-between font-body font-bold text-lg">
                      <span class="text-on-surface-variant">{{ __('Subtotal') }}</span>
                      <span class="text-on-surface tabular-nums">Rp 750.000</span>
                    </div>
                    <div class="flex items-center justify-between font-body font-bold text-lg">
                      <span class="text-on-surface-variant">{{ __('Admin Fee (Virtual Account)') }}</span>
                      <span class="text-on-surface tabular-nums">Rp 4.500</span>
                    </div>
                    <div
                      class="mt-2 flex items-center justify-between rounded-xl bg-navy px-5 py-4 shadow-sm shadow-navy/20">
                      <span class="font-heading text-sm font-semibold text-white/80 uppercase tracking-wider">
                        {{ __('Total Due') }}</span>
                      <span class="font-heading text-2xl font-bold text-white tabular-nums">Rp 754.500</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-surface-container rounded-xl p-6 flex items-start gap-4">
              <span class="material-symbols-outlined text-gold-dark mt-1">info</span>
              <div>
                <h4 class="font-body text-sm font-semibold text-on-surface mb-1">{{ __('Payment Verification') }}
                </h4>
                <p class="font-body text-sm text-on-surface-variant">
                  {{ __('Payments made via Virtual Account or QRIS are verified automatically within 10-15 minutes. Please retain your payment receipt until the status updates to "Lunas".') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      @endif

    </div>

    {{-- Right Column: Sidebar Actions --}}
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-8">

      @if ($currentStep === 4)
        <div class="flex flex-col gap-6">
          <h3 class="font-heading text-xl font-semibold text-on-surface mb-2">{{ __('Select Payment Method') }}
          </h3>
          <div class="flex flex-col gap-3">
            <label
              class="relative flex flex-col bg-white border {{ $payment_method === 'bca' ? 'border-navy ring-1 ring-navy' : 'border-outline-variant/50 hover:border-navy opacity-70' }} rounded-xl p-4 cursor-pointer transition-colors">
              <div class="flex items-center justify-between {{ $payment_method === 'bca' ? 'mb-3' : '' }}">
                <div class="flex items-center gap-3">
                  <input wire:model.live="payment_method" class="text-navy focus:ring-navy h-4 w-4 border-outline"
                    name="payment_method" type="radio" value="bca">
                  <span class="font-body text-sm font-semibold text-on-surface">BCA Virtual Account</span>
                </div>
                <div
                  class="w-10 h-10 bg-surface-container rounded flex items-center justify-center font-bold text-on-surface-variant text-[10px]">
                  BCA</div>
              </div>
              @if ($payment_method === 'bca')
                <div class="pl-7">
                  <div
                    class="font-heading text-lg font-semibold text-on-surface tracking-widest bg-surface-container p-2 rounded inline-block">
                    089 2384 1928 44</div>
                  <p class="font-body text-xs text-on-surface-variant mt-1">Expires in 23:59:12</p>
                </div>
              @endif
            </label>

            <label
              class="relative flex flex-col bg-white border {{ $payment_method === 'mandiri' ? 'border-navy ring-1 ring-navy' : 'border-outline-variant/50 hover:border-navy opacity-70' }} rounded-xl p-4 cursor-pointer transition-colors">
              <div class="flex items-center justify-between {{ $payment_method === 'mandiri' ? 'mb-3' : '' }}">
                <div class="flex items-center gap-3">
                  <input wire:model.live="payment_method" class="text-navy focus:ring-navy h-4 w-4 border-outline"
                    name="payment_method" type="radio" value="mandiri">
                  <span class="font-body text-sm font-semibold text-on-surface">Mandiri Virtual Account</span>
                </div>
                <div
                  class="w-10 h-10 bg-surface-container rounded flex items-center justify-center font-bold text-on-surface-variant text-[10px]">
                  MNDR</div>
              </div>
              @if ($payment_method === 'mandiri')
                <div class="pl-7">
                  <div
                    class="font-heading text-lg font-semibold text-on-surface tracking-widest bg-surface-container p-2 rounded inline-block">
                    8928 1192 8493</div>
                  <p class="font-body text-xs text-on-surface-variant mt-1">Expires in 23:59:12</p>
                </div>
              @endif
            </label>

            <label
              class="relative flex flex-col bg-white border {{ $payment_method === 'bni' ? 'border-navy ring-1 ring-navy' : 'border-outline-variant/50 hover:border-navy opacity-70' }} rounded-xl p-4 cursor-pointer transition-colors">
              <div class="flex items-center justify-between {{ $payment_method === 'bni' ? 'mb-3' : '' }}">
                <div class="flex items-center gap-3">
                  <input wire:model.live="payment_method" class="text-navy focus:ring-navy h-4 w-4 border-outline"
                    name="payment_method" type="radio" value="bni">
                  <span class="font-body text-sm font-semibold text-on-surface">BNI Virtual Account</span>
                </div>
                <div
                  class="w-10 h-10 bg-surface-container rounded flex items-center justify-center font-bold text-on-surface-variant text-[10px]">
                  BNI</div>
              </div>
              @if ($payment_method === 'bni')
                <div class="pl-7">
                  <div
                    class="font-heading text-lg font-semibold text-on-surface tracking-widest bg-surface-container p-2 rounded inline-block">
                    988 2938 4810</div>
                  <p class="font-body text-xs text-on-surface-variant mt-1">Expires in 23:59:12</p>
                </div>
              @endif
            </label>

            <label
              class="relative flex flex-col bg-white border {{ $payment_method === 'qris' ? 'border-navy ring-1 ring-navy' : 'border-outline-variant/50 hover:border-navy opacity-70' }} rounded-xl p-4 cursor-pointer transition-colors">
              <div class="flex items-center justify-between {{ $payment_method === 'qris' ? 'mb-3' : '' }}">
                <div class="flex items-center gap-3">
                  <input wire:model.live="payment_method" class="text-navy focus:ring-navy h-4 w-4 border-outline"
                    name="payment_method" type="radio" value="qris">
                  <span class="font-body text-sm font-semibold text-on-surface">QRIS</span>
                </div>
                <span class="material-symbols-outlined text-on-surface-variant">qr_code_2</span>
              </div>
              @if ($payment_method === 'qris')
                <div class="pl-7 mt-3 flex justify-center">
                  <div
                    class="w-32 h-32 bg-surface-container-high rounded border border-outline-variant flex items-center justify-center">
                    <span class="material-symbols-outlined text-[48px] text-on-surface-variant">qr_code_2</span>
                  </div>
                </div>
              @endif
            </label>
          </div>

          @if ($payment_status === 'awaiting')
            <button wire:click="simulatePayment" wire:loading.attr="disabled"
              class="mt-6 w-full py-3 px-6 bg-navy text-white hover:bg-navy-dark font-body text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 relative overflow-hidden group">
              <span class="relative z-10 flex items-center gap-2" wire:loading.remove wire:target="simulatePayment">
                <span>Simulate Payment (Auto-Lunas)</span>
                <span class="material-symbols-outlined text-[18px]">bolt</span>
              </span>
              <span class="relative z-10 flex items-center gap-2" wire:loading wire:target="simulatePayment">
                <span class="material-symbols-outlined text-[18px] animate-spin">sync</span>
                <span>Processing...</span>
              </span>
              <div
                class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out z-0">
              </div>
            </button>
          @else
            <div
              class="mt-6 w-full py-3 px-6 bg-green-100 text-green-800 font-body text-sm font-semibold rounded-lg flex items-center justify-center gap-2">
              <span class="material-symbols-outlined text-[18px]">check_circle</span>
              <span>{{ __('Payment Confirmed') }}</span>
            </div>
          @endif
        </div>
      @endif

      @if ($currentStep === 2)
        {{-- Required Documents Card (Preview in Step 2) --}}
        <div class="bg-white rounded-xl shadow-sm p-8 border border-outline-variant/30">
          <h3
            class="font-heading text-lg font-semibold text-navy border-b border-surface-container-high pb-3 mb-6 flex justify-between items-center">
            {{ __('Required Documents') }}
            <span class="material-symbols-outlined text-on-surface-variant">help</span>
          </h3>
          <p class="font-body text-sm text-on-surface-variant mb-6">
            {{ __('You will need to upload these documents in the next step. Max 5MB per file (PDF, JPG, PNG).') }}
          </p>

          <div class="space-y-3">
            {{-- Akta Kelahiran --}}
            <div class="p-3 rounded-lg border border-outline-variant flex items-center justify-between opacity-70">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-royal-pale/20 flex items-center justify-center text-navy">
                  <span class="material-symbols-outlined">description</span>
                </div>
                <div class="flex flex-col">
                  <span class="font-body text-sm font-semibold text-on-surface">{{ __('Akta Kelahiran') }}</span>
                  <span
                    class="text-[10px] font-bold text-red-600 uppercase tracking-tighter">{{ __('Required') }}</span>
                </div>
              </div>
            </div>

            {{-- Kartu Keluarga --}}
            <div class="p-3 rounded-lg border border-outline-variant flex items-center justify-between opacity-70">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-royal-pale/20 flex items-center justify-center text-navy">
                  <span class="material-symbols-outlined">group</span>
                </div>
                <div class="flex flex-col">
                  <span class="font-body text-sm font-semibold text-on-surface">{{ __('Kartu Keluarga') }}</span>
                  <span
                    class="text-[10px] font-bold text-red-600 uppercase tracking-tighter">{{ __('Required') }}</span>
                </div>
              </div>
            </div>

            {{-- Ijazah & Transkrip Nilai --}}
            <div class="p-3 rounded-lg border border-outline-variant flex items-center justify-between opacity-70">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-royal-pale/20 flex items-center justify-center text-navy">
                  <span class="material-symbols-outlined">school</span>
                </div>
                <div class="flex flex-col">
                  <span
                    class="font-body text-sm font-semibold text-on-surface">{{ __('Ijazah & Transkrip Nilai') }}</span>
                  <span
                    class="text-[10px] font-bold text-red-600 uppercase tracking-tighter">{{ __('Required') }}</span>
                </div>
              </div>
            </div>

            {{-- Pas Foto 4x6 --}}
            <div class="p-3 rounded-lg border border-outline-variant flex items-center justify-between opacity-70">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-royal-pale/20 flex items-center justify-center text-navy">
                  <span class="material-symbols-outlined">portrait</span>
                </div>
                <div class="flex flex-col">
                  <span class="font-body text-sm font-semibold text-on-surface">{{ __('Pas Foto 4x6') }}</span>
                  <span
                    class="text-[10px] font-bold text-red-600 uppercase tracking-tighter">{{ __('Required') }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      {{-- Action Card --}}
      <div class="bg-white rounded-xl shadow-sm p-8 border border-outline-variant/30 sticky top-8">
        <h3 class="font-heading text-lg font-semibold text-navy mb-4">{{ __('Actions') }}</h3>

        <div class="flex flex-col gap-3">
          @if ($currentStep === 2)
            <flux:button wire:click.prevent="saveAndContinue" variant="primary" class="w-full">
              <div class="flex items-center justify-center gap-2 w-full">
                <span>{{ __('Save & Continue') }}</span>
                <span class="material-symbols-outlined text-[20px] pt-0.5">arrow_forward</span>
              </div>
            </flux:button>
          @elseif ($currentStep === 3)
            <flux:button wire:click.prevent="saveAndContinue" variant="primary" class="w-full"
              wire:loading.attr="disabled">
              <div class="flex items-center justify-center gap-2 w-full">
                <span wire:loading.remove wire:target="saveAndContinue">{{ __('Confirm Documents') }}</span>
                <span wire:loading wire:target="saveAndContinue">{{ __('Uploading...') }}</span>
                <span class="material-symbols-outlined text-[20px] pt-0.5" wire:loading.remove
                  wire:target="saveAndContinue">arrow_forward</span>
              </div>
            </flux:button>
          @endif

          <flux:button wire:click.prevent="saveAsDraft" variant="outline" class="w-full">
            <div class="flex items-center justify-center gap-2 w-full">
              <span>{{ __('Save as Draft') }}</span>
              <span class="material-symbols-outlined text-[20px] pt-0.5">save</span>
            </div>
          </flux:button>

          @if ($currentStep > 2)
            <flux:button wire:click.prevent="previousStep" variant="subtle" class="w-full mt-2">
              <div class="flex items-center justify-center gap-2 w-full">
                <span class="material-symbols-outlined text-[20px] pt-0.5">arrow_back</span>
                <span>{{ __('Back to Previous Step') }}</span>
              </div>
            </flux:button>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
