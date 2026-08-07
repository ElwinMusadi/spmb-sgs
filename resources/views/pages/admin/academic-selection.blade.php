<?php

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\RequiredDocument;
use App\Models\SchoolLevel;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Academic Selection')] class extends Component {
    public string $activeTab = 'years';

    public bool $showYearModal = false;
    public ?int $editingYearId = null;
    public string $yearName = '';
    public bool $yearIsActive = true;

    public bool $showLevelModal = false;
    public ?int $editingLevelId = null;
    public string $levelName = '';
    public string $levelCode = '';
    public string $levelDescription = '';
    public bool $levelIsActive = true;

    public bool $showGradeModal = false;
    public ?int $editingGradeId = null;
    public string $gradeSchoolLevelId = '';
    public string $gradeName = '';
    public string $gradeCode = '';
    public bool $gradeIsActive = true;
    public int $gradeSortOrder = 0;

    public bool $showDocumentModal = false;
    public ?int $editingDocumentId = null;
    public string $documentType = '';
    public string $documentName = '';
    public string $documentDescription = '';
    public string $documentExtensions = 'pdf,jpg,jpeg,png';
    public bool $documentIsRequired = true;
    public bool $documentIsActive = true;
    public int $documentSortOrder = 0;

    public bool $showDeleteModal = false;
    public ?string $deleteType = null;
    public ?int $deleteId = null;

    public function mount(): mixed
    {
        if (! Auth::user()->can('academic_selection.manage')) {
            return $this->redirectRoute(Auth::user()->homeRoute(), navigate: true);
        }

        return null;
    }

    #[Computed]
    public function years()
    {
        return AcademicYear::orderByDesc('name')->get();
    }

    #[Computed]
    public function levels()
    {
        return SchoolLevel::withCount('grades')->orderBy('id')->get();
    }

    #[Computed]
    public function grades()
    {
        return Grade::with('schoolLevel')->orderBy('school_level_id')->orderBy('sort_order')->get();
    }

    #[Computed]
    public function documents()
    {
        return RequiredDocument::orderBy('sort_order')->get();
    }

    public function createYear(): void
    {
        $this->resetYearForm();
        $this->showYearModal = true;
    }

    public function editYear(int $id): void
    {
        $year = AcademicYear::findOrFail($id);
        $this->editingYearId = $year->id;
        $this->yearName = $year->name;
        $this->yearIsActive = $year->is_active;
        $this->resetErrorBag();
        $this->showYearModal = true;
    }

    public function saveYear(): void
    {
        $validated = $this->validate([
            'yearName' => ['required', 'string', 'max:255', Rule::unique('academic_years', 'name')->ignore($this->editingYearId)],
            'yearIsActive' => ['boolean'],
        ]);

        AcademicYear::updateOrCreate(
            ['id' => $this->editingYearId],
            ['name' => $validated['yearName'], 'is_active' => $validated['yearIsActive']],
        );
        $this->showYearModal = false;
        $this->resetYearForm();
        unset($this->years);
        Flux::toast(variant: 'success', text: __('Academic year saved.'));
    }

    public function createLevel(): void
    {
        $this->resetLevelForm();
        $this->showLevelModal = true;
    }

    public function editLevel(int $id): void
    {
        $level = SchoolLevel::findOrFail($id);
        $this->editingLevelId = $level->id;
        $this->levelName = $level->name;
        $this->levelCode = $level->code;
        $this->levelDescription = $level->description ?? '';
        $this->levelIsActive = $level->is_active;
        $this->resetErrorBag();
        $this->showLevelModal = true;
    }

    public function saveLevel(): void
    {
        $validated = $this->validate([
            'levelName' => ['required', 'string', 'max:255'],
            'levelCode' => ['required', 'string', 'max:10', Rule::unique('school_levels', 'code')->ignore($this->editingLevelId)],
            'levelDescription' => ['nullable', 'string', 'max:1000'],
            'levelIsActive' => ['boolean'],
        ]);

        SchoolLevel::updateOrCreate(
            ['id' => $this->editingLevelId],
            ['name' => $validated['levelName'], 'code' => strtoupper($validated['levelCode']), 'description' => $validated['levelDescription'] ?: null, 'is_active' => $validated['levelIsActive']],
        );
        $this->showLevelModal = false;
        $this->resetLevelForm();
        unset($this->levels, $this->grades);
        Flux::toast(variant: 'success', text: __('School level saved.'));
    }

    public function createGrade(): void
    {
        $this->resetGradeForm();
        $this->showGradeModal = true;
    }

    public function editGrade(int $id): void
    {
        $grade = Grade::findOrFail($id);
        $this->editingGradeId = $grade->id;
        $this->gradeSchoolLevelId = (string) $grade->school_level_id;
        $this->gradeName = $grade->name;
        $this->gradeCode = $grade->code;
        $this->gradeIsActive = $grade->is_active;
        $this->gradeSortOrder = $grade->sort_order;
        $this->resetErrorBag();
        $this->showGradeModal = true;
    }

    public function saveGrade(): void
    {
        $validated = $this->validate([
            'gradeSchoolLevelId' => ['required', 'exists:school_levels,id'],
            'gradeName' => ['required', 'string', 'max:255'],
            'gradeCode' => ['required', 'string', 'max:30'],
            'gradeIsActive' => ['boolean'],
            'gradeSortOrder' => ['integer', 'min:0', 'max:999'],
        ]);

        $duplicate = Grade::where('school_level_id', $validated['gradeSchoolLevelId'])
            ->where('code', $validated['gradeCode'])
            ->when($this->editingGradeId, fn ($query) => $query->where('id', '!=', $this->editingGradeId))
            ->exists();

        if ($duplicate) {
            $this->addError('gradeCode', __('This grade code already exists for the selected level.'));

            return;
        }

        Grade::updateOrCreate(
            ['id' => $this->editingGradeId],
            ['school_level_id' => $validated['gradeSchoolLevelId'], 'name' => $validated['gradeName'], 'code' => $validated['gradeCode'], 'is_active' => $validated['gradeIsActive'], 'sort_order' => $validated['gradeSortOrder']],
        );
        $this->showGradeModal = false;
        $this->resetGradeForm();
        unset($this->grades);
        Flux::toast(variant: 'success', text: __('Grade saved.'));
    }

    public function createDocument(): void
    {
        $this->resetDocumentForm();
        $this->showDocumentModal = true;
    }

    public function editDocument(int $id): void
    {
        $document = RequiredDocument::findOrFail($id);
        $this->editingDocumentId = $document->id;
        $this->documentType = $document->document_type;
        $this->documentName = $document->name;
        $this->documentDescription = $document->description ?? '';
        $this->documentExtensions = implode(',', $document->allowed_extensions);
        $this->documentIsRequired = $document->is_required;
        $this->documentIsActive = $document->is_active;
        $this->documentSortOrder = $document->sort_order;
        $this->resetErrorBag();
        $this->showDocumentModal = true;
    }

    public function saveDocument(): void
    {
        $validated = $this->validate([
            'documentType' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+$/', Rule::unique('required_documents', 'document_type')->ignore($this->editingDocumentId)],
            'documentName' => ['required', 'string', 'max:255'],
            'documentDescription' => ['nullable', 'string', 'max:1000'],
            'documentExtensions' => ['required', 'string', 'max:255'],
            'documentIsRequired' => ['boolean'],
            'documentIsActive' => ['boolean'],
            'documentSortOrder' => ['integer', 'min:0', 'max:999'],
        ]);

        $extensions = collect(explode(',', strtolower($validated['documentExtensions'])))
            ->map(fn (string $extension): string => ltrim(trim($extension), '.'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($extensions === []) {
            $this->addError('documentExtensions', __('Add at least one supported extension.'));

            return;
        }

        RequiredDocument::updateOrCreate(
            ['id' => $this->editingDocumentId],
            ['document_type' => $validated['documentType'], 'name' => $validated['documentName'], 'description' => $validated['documentDescription'] ?: null, 'allowed_extensions' => $extensions, 'is_required' => $validated['documentIsRequired'], 'is_active' => $validated['documentIsActive'], 'sort_order' => $validated['documentSortOrder']],
        );
        $this->showDocumentModal = false;
        $this->resetDocumentForm();
        unset($this->documents);
        Flux::toast(variant: 'success', text: __('Required document saved.'));
    }

    public function confirmDelete(string $type, int $id): void
    {
        $this->deleteType = $type;
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteRecord(): void
    {
        $this->validateDeleteTarget();

        match ($this->deleteType) {
            'year' => AcademicYear::findOrFail($this->deleteId)->delete(),
            'level' => SchoolLevel::findOrFail($this->deleteId)->delete(),
            'grade' => Grade::findOrFail($this->deleteId)->delete(),
            'document' => RequiredDocument::findOrFail($this->deleteId)->delete(),
            default => null,
        };
        $this->showDeleteModal = false;
        $this->deleteType = null;
        $this->deleteId = null;
        unset($this->years, $this->levels, $this->grades, $this->documents);
        Flux::toast(variant: 'success', text: __('Record deleted.'));
    }

    private function validateDeleteTarget(): void
    {
        if (! $this->deleteId || ! $this->deleteType) {
            return;
        }

        $inUse = match ($this->deleteType) {
            'year' => AcademicYear::whereKey($this->deleteId)->whereHas('applications')->exists(),
            'level' => SchoolLevel::whereKey($this->deleteId)->whereHas('applications')->exists(),
            'grade', 'document' => false,
            default => false,
        };

        if ($inUse) {
            $this->showDeleteModal = false;
            Flux::toast(variant: 'warning', text: __('This record is already used by an application and cannot be deleted.'));
            $this->deleteId = null;
            $this->deleteType = null;
            abort(422);
        }
    }

    private function resetYearForm(): void
    {
        $this->editingYearId = null;
        $this->yearName = '';
        $this->yearIsActive = true;
        $this->resetErrorBag();
    }

    private function resetLevelForm(): void
    {
        $this->editingLevelId = null;
        $this->levelName = '';
        $this->levelCode = '';
        $this->levelDescription = '';
        $this->levelIsActive = true;
        $this->resetErrorBag();
    }

    private function resetGradeForm(): void
    {
        $this->editingGradeId = null;
        $this->gradeSchoolLevelId = '';
        $this->gradeName = '';
        $this->gradeCode = '';
        $this->gradeIsActive = true;
        $this->gradeSortOrder = 0;
        $this->resetErrorBag();
    }

    private function resetDocumentForm(): void
    {
        $this->editingDocumentId = null;
        $this->documentType = '';
        $this->documentName = '';
        $this->documentDescription = '';
        $this->documentExtensions = 'pdf,jpg,jpeg,png';
        $this->documentIsRequired = true;
        $this->documentIsActive = true;
        $this->documentSortOrder = 0;
        $this->resetErrorBag();
    }
}; ?>

<div class="flex flex-col w-full max-w-7xl mx-auto gap-8 pb-16">
  <div class="flex flex-col gap-1">
    <div class="flex items-center gap-3">
      <span class="material-symbols-outlined text-navy text-[32px]">tune</span>
      <h1 class="font-heading text-3xl font-semibold text-navy">{{ __('Academic Selection') }}</h1>
    </div>
    <p class="font-body text-base text-on-surface-variant">{{ __('Manage academic years, school levels, grades, and required document rules.') }}</p>
  </div>

  <div class="flex flex-wrap gap-2">
    @foreach (['years' => __('Academic Years'), 'levels' => __('School Levels'), 'grades' => __('Grades'), 'documents' => __('Required Documents')] as $tab => $label)
      <button wire:click="$set('activeTab', '{{ $tab }}')" class="px-4 py-2.5 rounded-lg font-body text-sm font-semibold {{ $activeTab === $tab ? 'bg-navy text-white' : 'bg-white border border-outline-variant/40 text-on-surface-variant hover:border-navy/50' }}">{{ $label }}</button>
    @endforeach
  </div>

  @if ($activeTab === 'years')
    @include('pages.admin.academic-selection.years')
  @elseif ($activeTab === 'levels')
    @include('pages.admin.academic-selection.levels')
  @elseif ($activeTab === 'grades')
    @include('pages.admin.academic-selection.grades')
  @else
    @include('pages.admin.academic-selection.documents')
  @endif

  <flux:modal wire:model="showYearModal" class="min-w-[28rem]"><form wire:submit="saveYear" class="flex flex-col gap-5"><flux:heading size="lg">{{ $editingYearId ? __('Edit Academic Year') : __('Add Academic Year') }}</flux:heading><flux:input wire:model="yearName" :label="__('Name')" placeholder="2026 / 2027" /><flux:switch wire:model="yearIsActive" :label="__('Active')" /><div class="flex justify-end gap-3"><flux:button type="button" variant="ghost" wire:click="$set('showYearModal', false)">{{ __('Cancel') }}</flux:button><flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button></div></form></flux:modal>
  <flux:modal wire:model="showLevelModal" class="min-w-[28rem]"><form wire:submit="saveLevel" class="flex flex-col gap-5"><flux:heading size="lg">{{ $editingLevelId ? __('Edit School Level') : __('Add School Level') }}</flux:heading><flux:input wire:model="levelName" :label="__('Name')" /><flux:input wire:model="levelCode" :label="__('Code')" /><flux:textarea wire:model="levelDescription" :label="__('Description')" rows="2" /><flux:switch wire:model="levelIsActive" :label="__('Active')" /><div class="flex justify-end gap-3"><flux:button type="button" variant="ghost" wire:click="$set('showLevelModal', false)">{{ __('Cancel') }}</flux:button><flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button></div></form></flux:modal>
  <flux:modal wire:model="showGradeModal" class="min-w-[28rem]"><form wire:submit="saveGrade" class="flex flex-col gap-5"><flux:heading size="lg">{{ $editingGradeId ? __('Edit Grade') : __('Add Grade') }}</flux:heading><flux:select wire:model="gradeSchoolLevelId" :label="__('School Level')"><flux:select.option value="">{{ __('Select level') }}</flux:select.option>@foreach ($this->levels as $level)<flux:select.option value="{{ $level->id }}">{{ $level->code }} - {{ $level->name }}</flux:select.option>@endforeach</flux:select><flux:input wire:model="gradeName" :label="__('Name')" /><flux:input wire:model="gradeCode" :label="__('Code')" /><flux:input wire:model="gradeSortOrder" type="number" min="0" :label="__('Sort Order')" /><flux:switch wire:model="gradeIsActive" :label="__('Active')" /><div class="flex justify-end gap-3"><flux:button type="button" variant="ghost" wire:click="$set('showGradeModal', false)">{{ __('Cancel') }}</flux:button><flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button></div></form></flux:modal>
  <flux:modal wire:model="showDocumentModal" class="min-w-[32rem]"><form wire:submit="saveDocument" class="flex flex-col gap-5"><flux:heading size="lg">{{ $editingDocumentId ? __('Edit Required Document') : __('Add Required Document') }}</flux:heading><flux:input wire:model="documentType" :label="__('Type Key')" placeholder="akta_kelahiran" /><flux:input wire:model="documentName" :label="__('Name')" /><flux:textarea wire:model="documentDescription" :label="__('Description')" rows="2" /><flux:input wire:model="documentExtensions" :label="__('Allowed Extensions')" placeholder="pdf,jpg,jpeg,png" /><flux:text class="text-xs">{{ __('Separate extensions with commas. Do not include dots.') }}</flux:text><div class="grid grid-cols-2 gap-4"><flux:input wire:model="documentSortOrder" type="number" min="0" :label="__('Sort Order')" /><flux:switch wire:model="documentIsRequired" :label="__('Required')" /></div><flux:switch wire:model="documentIsActive" :label="__('Active')" /><div class="flex justify-end gap-3"><flux:button type="button" variant="ghost" wire:click="$set('showDocumentModal', false)">{{ __('Cancel') }}</flux:button><flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button></div></form></flux:modal>
  <flux:modal wire:model="showDeleteModal" class="min-w-[24rem]"><div class="flex flex-col gap-6"><flux:heading size="lg">{{ __('Delete Record') }}</flux:heading><flux:text>{{ __('This action cannot be undone.') }}</flux:text><div class="flex justify-end gap-3"><flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">{{ __('Cancel') }}</flux:button><flux:button variant="danger" wire:click="deleteRecord">{{ __('Delete') }}</flux:button></div></div></flux:modal>
</div>
