<?php

use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    /**
     * Only applicants (students) may access the applicant dashboard.
     */
    public function mount(): mixed
    {
        if (! Auth::user()->isStudent()) {
            return $this->redirectRoute(Auth::user()->homeRoute(), navigate: true);
        }

        return null;
    }

    /**
     * Required document types and their display labels.
     *
     * @var array<string, string>
     */
    private const REQUIRED_DOCUMENTS = [
        'akta_kelahiran' => 'Akta Kelahiran',
        'kartu_keluarga' => 'Kartu Keluarga',
        'ijazah' => 'Ijazah & Transkrip Nilai',
        'pas_foto' => 'Pas Foto 4x6',
    ];

    /**
     * Get the current user's latest application.
     */
    #[Computed]
    public function application(): ?Application
    {
        return Auth::user()
            ->applications()
            ->with(['documents', 'schoolLevel', 'academicYear'])
            ->latest()
            ->first();
    }

    /**
     * Get the list of document types the applicant has already uploaded.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function uploadedDocumentTypes(): array
    {
        if (! $this->application) {
            return [];
        }

        return $this->application->documents->pluck('document_type')->all();
    }

    /**
     * Build the applicant task list with completion state.
     *
     * @return array<int, array{key: string, label: string, description: string, icon: string, done: bool, route: string|null}>
     */
    #[Computed]
    public function tasks(): array
    {
        $application = $this->application;
        $step = $application?->current_step ?? 0;
        $uploaded = $this->uploadedDocumentTypes;

        $tasks = [
            [
                'key' => 'account',
                'label' => __('Create Account'),
                'description' => __('Portal account successfully registered.'),
                'icon' => 'person_add',
                'done' => true,
                'route' => null,
            ],
            [
                'key' => 'form',
                'label' => __('Complete Application Form'),
                'description' => __('Fill out student, academic, and family details.'),
                'icon' => 'edit_document',
                'done' => $application !== null && $step > 2,
                'route' => route('application'),
            ],
        ];

        foreach (self::REQUIRED_DOCUMENTS as $type => $label) {
            $tasks[] = [
                'key' => "doc_{$type}",
                'label' => __('Upload :document', ['document' => $label]),
                'description' => __('Submit a clear scan or photo (PDF, JPG, PNG).'),
                'icon' => 'upload_file',
                'done' => in_array($type, $uploaded, true),
                'route' => route('application'),
            ];
        }

        $tasks[] = [
            'key' => 'payment',
            'label' => __('Complete Registration Payment'),
            'description' => __('Pay the registration fee to secure your slot.'),
            'icon' => 'payments',
            'done' => $application?->payment_status === 'paid',
            'route' => route('application'),
        ];

        return $tasks;
    }

    /**
     * Count of completed tasks.
     */
    #[Computed]
    public function completedTaskCount(): int
    {
        return count(array_filter($this->tasks, fn (array $task): bool => $task['done']));
    }

    /**
     * Total number of tasks.
     */
    #[Computed]
    public function totalTaskCount(): int
    {
        return count($this->tasks);
    }

    /**
     * Overall completion percentage.
     */
    #[Computed]
    public function progressPercentage(): int
    {
        if ($this->totalTaskCount === 0) {
            return 0;
        }

        return (int) round(($this->completedTaskCount / $this->totalTaskCount) * 100);
    }

    /**
     * Build the document checklist with per-document status.
     *
     * @return array<int, array{type: string, label: string, status: string}>
     */
    #[Computed]
    public function documentChecklist(): array
    {
        $uploaded = $this->uploadedDocumentTypes;

        $checklist = [];
        foreach (self::REQUIRED_DOCUMENTS as $type => $label) {
            $checklist[] = [
                'type' => $type,
                'label' => $label,
                'status' => in_array($type, $uploaded, true) ? 'uploaded' : 'missing',
            ];
        }

        return $checklist;
    }

    /**
     * Count of uploaded required documents.
     */
    #[Computed]
    public function uploadedDocumentCount(): int
    {
        return count($this->uploadedDocumentTypes);
    }

    /**
     * The next action-required task, if any.
     *
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function nextTask(): ?array
    {
        foreach ($this->tasks as $task) {
            if (! $task['done']) {
                return $task;
            }
        }

        return null;
    }
}; ?>

<div class="flex flex-col w-full max-w-7xl mx-auto gap-8 pb-16">

    {{-- Welcome / Progress Hero --}}
    <section
      class="relative overflow-hidden rounded-2xl bg-navy p-8 flex flex-col md:flex-row md:items-center justify-between gap-8">
      <div class="absolute top-0 right-0 w-64 h-64 bg-royal-light/20 rounded-full blur-3xl opacity-40 -mr-16 -mt-16">
      </div>
      <div class="absolute bottom-0 left-24 w-48 h-48 bg-gold/20 rounded-full blur-3xl opacity-30 -mb-16"></div>

      <div class="relative z-10 flex-1">
        <h1 class="font-heading text-3xl font-semibold text-white mb-2">
          {{ __('Welcome, :name!', ['name' => auth()->user()->name]) }}
        </h1>
        <p class="font-body text-base text-royal-dim max-w-2xl">
          @if ($this->application)
            {{ __('Your application is in progress. Review your pending tasks below to complete your submission.') }}
          @else
            {{ __('Welcome to Stella Gracia School. Start your application now to begin the admission process.') }}
          @endif
        </p>

        @if (! $this->application)
          <a href="{{ route('application') }}"
            class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-white text-navy font-body text-sm font-semibold hover:bg-royal-pale transition-colors">
            <span class="material-symbols-outlined text-[20px]">rocket_launch</span>
            {{ __('Start Application') }}
          </a>
        @elseif ($this->nextTask)
          <a href="{{ $this->nextTask['route'] ?? route('application') }}"
            class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-white text-navy font-body text-sm font-semibold hover:bg-royal-pale transition-colors">
            <span class="material-symbols-outlined text-[20px]">{{ $this->nextTask['icon'] }}</span>
            {{ $this->nextTask['label'] }}
          </a>
        @endif
      </div>

      {{-- Overall Progress Widget --}}
      <div class="relative z-10 bg-white/10 backdrop-blur-md rounded-xl p-6 min-w-[280px]">
        <div class="flex justify-between items-center mb-3">
          <span class="font-body text-xs font-semibold text-white uppercase tracking-wider">
            {{ __('Overall Progress') }}</span>
          <span class="font-heading text-2xl font-bold text-white">{{ $this->progressPercentage }}%</span>
        </div>
        <div class="w-full h-2.5 bg-white/20 rounded-full overflow-hidden mb-2">
          <div class="h-full bg-gold rounded-full transition-all duration-700 ease-out"
            style="width: {{ $this->progressPercentage }}%;"></div>
        </div>
        <p class="font-body text-xs text-royal-dim text-right">
          {{ __(':done of :total tasks completed', ['done' => $this->completedTaskCount, 'total' => $this->totalTaskCount]) }}
        </p>
      </div>
    </section>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

      {{-- Left Column --}}
      <div class="lg:col-span-8 flex flex-col gap-8">

        {{-- Action Required --}}
        <div>
          <h2 class="font-heading text-xl font-semibold text-on-surface mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-navy">bolt</span>
            {{ __('Action Required') }}
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Complete Form --}}
            @php($formDone = $this->application && $this->application->current_step > 2)
            <a href="{{ route('application') }}"
              class="group relative bg-white p-5 rounded-xl shadow-sm border border-outline-variant/30 hover:shadow-md transition-all flex flex-col overflow-hidden">
              <div
                class="w-12 h-12 rounded-lg bg-navy/10 flex items-center justify-center mb-4 group-hover:bg-navy group-hover:text-white text-navy transition-colors">
                <span class="material-symbols-outlined text-[26px]">edit_document</span>
              </div>
              <h3 class="font-heading text-base font-semibold text-on-surface mb-1 group-hover:text-navy transition-colors">
                {{ __('Application Form') }}</h3>
              <p class="font-body text-sm text-on-surface-variant flex-1 mb-4">
                {{ __('Fill out personal, academic, and family details.') }}</p>
              <div class="flex items-center justify-between mt-auto">
                @if ($formDone)
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-body text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ __('Completed') }}
                  </span>
                @else
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 text-red-700 font-body text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ __('Incomplete') }}
                  </span>
                @endif
                <span
                  class="material-symbols-outlined text-navy group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </div>
            </a>

            {{-- Upload Documents --}}
            @php($docsDone = $this->uploadedDocumentCount >= count($this->documentChecklist))
            <a href="{{ route('application') }}"
              class="group relative bg-white p-5 rounded-xl shadow-sm border border-outline-variant/30 hover:shadow-md transition-all flex flex-col overflow-hidden">
              <div
                class="w-12 h-12 rounded-lg bg-navy/10 flex items-center justify-center mb-4 group-hover:bg-navy group-hover:text-white text-navy transition-colors">
                <span class="material-symbols-outlined text-[26px]">folder_shared</span>
              </div>
              <h3 class="font-heading text-base font-semibold text-on-surface mb-1 group-hover:text-navy transition-colors">
                {{ __('Upload Documents') }}</h3>
              <p class="font-body text-sm text-on-surface-variant flex-1 mb-4">
                {{ __('Birth certificate, family card, report cards, and photo.') }}</p>
              <div class="flex items-center justify-between mt-auto">
                @if ($docsDone)
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-body text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ __('Completed') }}
                  </span>
                @else
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gold-pale text-gold-dark font-body text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-gold"></span>
                    {{ __(':count left', ['count' => count($this->documentChecklist) - $this->uploadedDocumentCount]) }}
                  </span>
                @endif
                <span
                  class="material-symbols-outlined text-navy group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </div>
            </a>

            {{-- Registration Fee --}}
            @php($paid = $this->application?->payment_status === 'paid')
            <a href="{{ route('application') }}"
              class="group relative bg-white p-5 rounded-xl shadow-sm border border-outline-variant/30 hover:shadow-md transition-all flex flex-col overflow-hidden">
              <div
                class="w-12 h-12 rounded-lg bg-navy/10 flex items-center justify-center mb-4 group-hover:bg-navy group-hover:text-white text-navy transition-colors">
                <span class="material-symbols-outlined text-[26px]">payments</span>
              </div>
              <h3 class="font-heading text-base font-semibold text-on-surface mb-1 group-hover:text-navy transition-colors">
                {{ __('Registration Fee') }}</h3>
              <p class="font-body text-sm text-on-surface-variant flex-1 mb-4">
                {{ __('Pay the registration fee to secure your slot.') }}</p>
              <div class="flex items-center justify-between mt-auto">
                @if ($paid)
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-body text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ __('Paid') }}
                  </span>
                @else
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant font-body text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-outline"></span> {{ __('Awaiting Payment') }}
                  </span>
                @endif
                <span
                  class="material-symbols-outlined text-navy group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </div>
            </a>

            {{-- Admission Guide --}}
            <div
              class="relative bg-surface-container p-5 rounded-xl shadow-sm border border-outline-variant/30 flex flex-col overflow-hidden">
              <div class="w-12 h-12 rounded-lg bg-white shadow-sm flex items-center justify-center mb-4 text-gold-dark">
                <span class="material-symbols-outlined text-[26px]">menu_book</span>
              </div>
              <h3 class="font-heading text-base font-semibold text-on-surface mb-1">
                {{ __('Admission Guide') }} {{ date('Y') }}</h3>
              <p class="font-body text-sm text-on-surface-variant flex-1 mb-4">
                {{ __('Everything you need to know about the process.') }}</p>
              <a href="{{ route('home') }}#alur-pendaftaran"
                class="text-navy font-body text-sm font-semibold hover:text-royal transition-colors flex items-center gap-1.5 mt-auto">
                <span class="material-symbols-outlined text-[18px]">arrow_outward</span>
                {{ __('View Registration Flow') }}
              </a>
            </div>
          </div>
        </div>

        {{-- Admission Timeline --}}
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-outline-variant/30">
          <h2 class="font-heading text-xl font-semibold text-on-surface mb-8 flex items-center gap-2">
            <span class="material-symbols-outlined text-navy">route</span>
            {{ __('Admission Timeline') }}
          </h2>

          @php($step = $this->application?->current_step ?? 2)
          @php($paid = $this->application?->payment_status === 'paid')
          <div class="relative">
            <div class="absolute left-6 top-4 bottom-4 w-0.5 bg-surface-container-high"></div>

            <div class="space-y-8 relative z-10">
              {{-- Account Created --}}
              <div class="flex gap-5">
                <div
                  class="w-12 h-12 rounded-full bg-navy flex items-center justify-center shadow-md relative z-10 shrink-0 ring-4 ring-white">
                  <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">check</span>
                </div>
                <div class="pt-1.5 flex-1">
                  <h4 class="font-heading text-base font-semibold text-on-surface">{{ __('Account Created') }}</h4>
                  <p class="font-body text-sm text-on-surface-variant">
                    {{ __('Your application portal account was successfully registered.') }}</p>
                  <span
                    class="font-body text-xs text-outline mt-1 block">{{ auth()->user()->created_at?->format('M d, Y') }}</span>
                </div>
              </div>

              {{-- Application Submission --}}
              <div class="flex gap-5 {{ $paid ? '' : ($this->application ? '' : 'opacity-60') }}">
                @if ($paid)
                  <div
                    class="w-12 h-12 rounded-full bg-navy flex items-center justify-center shadow-md relative z-10 shrink-0 ring-4 ring-white">
                    <span class="material-symbols-outlined text-white"
                      style="font-variation-settings: 'FILL' 1;">check</span>
                  </div>
                @else
                  <div
                    class="w-12 h-12 rounded-full bg-white shadow-[0_0_0_2px_var(--color-navy)_inset] flex items-center justify-center relative z-10 shrink-0 ring-4 ring-white">
                    <span class="w-4 h-4 rounded-full bg-navy animate-pulse"></span>
                  </div>
                @endif
                <div class="pt-1.5 flex-1">
                  <h4 class="font-heading text-base font-semibold {{ $paid ? 'text-on-surface' : 'text-navy' }}">
                    {{ __('Application Submission') }}</h4>
                  <p class="font-body text-sm text-on-surface-variant">
                    {{ __('Complete forms, upload documents, and settle the registration fee.') }}</p>
                  @unless ($paid)
                    <div class="mt-2 p-2 bg-surface-container-lowest rounded-lg inline-block">
                      <span class="font-body text-xs text-on-surface flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-navy">bar_chart</span>
                        {{ __('Step :step of 4', ['step' => max(2, $step)]) }}
                      </span>
                    </div>
                  @endunless
                </div>
              </div>

              {{-- Interview & Assessment --}}
              <div class="flex gap-5 opacity-60">
                <div
                  class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center relative z-10 shrink-0 ring-4 ring-white">
                  <span class="font-body text-base font-semibold text-on-surface-variant">3</span>
                </div>
                <div class="pt-1.5 flex-1">
                  <h4 class="font-heading text-base font-semibold text-on-surface">
                    {{ __('Interview & Assessment') }}</h4>
                  <p class="font-body text-sm text-on-surface-variant">
                    {{ __('Attend the entrance assessment and interview with our academic team.') }}</p>
                </div>
              </div>

              {{-- Final Result --}}
              <div class="flex gap-5 opacity-60">
                <div
                  class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center relative z-10 shrink-0 ring-4 ring-white">
                  <span class="font-body text-base font-semibold text-on-surface-variant">4</span>
                </div>
                <div class="pt-1.5 flex-1">
                  <h4 class="font-heading text-base font-semibold text-on-surface">{{ __('Final Result') }}</h4>
                  <p class="font-body text-sm text-on-surface-variant">
                    {{ __('Receive the admission decision and enrollment instructions.') }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Right Column --}}
      <div class="lg:col-span-4 flex flex-col gap-8">

        {{-- Documents Checklist --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-outline-variant/30">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-heading text-lg font-semibold text-on-surface">{{ __('Documents') }}</h2>
            <a class="text-navy font-body text-sm font-semibold hover:text-royal transition-colors"
              href="{{ route('application') }}">{{ __('Manage') }}</a>
          </div>

          <div class="space-y-2">
            @foreach ($this->documentChecklist as $doc)
              @if ($doc['status'] === 'uploaded')
                <div class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-surface-container-lowest transition-colors">
                  <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-body text-sm font-medium text-on-surface truncate">{{ __($doc['label']) }}</p>
                    <p class="font-body text-xs text-green-600">{{ __('Uploaded') }}</p>
                  </div>
                </div>
              @else
                <div class="flex items-center gap-3 p-2.5 rounded-lg bg-red-50 hover:bg-red-100/60 transition-colors">
                  <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-red-600">upload_file</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-body text-sm font-medium text-on-surface truncate">{{ __($doc['label']) }}</p>
                    <p class="font-body text-xs text-red-600">{{ __('Missing') }}</p>
                  </div>
                  <a href="{{ route('application') }}"
                    class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-navy hover:bg-navy hover:text-white transition-colors shrink-0">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                  </a>
                </div>
              @endif
            @endforeach
          </div>

          {{-- Circular Progress --}}
          @php($total = count($this->documentChecklist))
          @php($uploaded = $this->uploadedDocumentCount)
          @php($dash = $total > 0 ? round(($uploaded / $total) * 100, 1) : 0)
          <div class="mt-6 pt-5 border-t border-outline-variant/30 flex items-center gap-4">
            <div class="relative w-20 h-20 shrink-0">
              <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                <path class="text-surface-container-high"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                  stroke="currentColor" stroke-width="3"></path>
                <path class="text-navy"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                  stroke="currentColor" stroke-dasharray="{{ $dash }}, 100" stroke-width="3"
                  stroke-linecap="round"></path>
              </svg>
              <div class="absolute inset-0 flex items-center justify-center">
                <span class="font-heading text-sm font-bold text-on-surface">{{ $uploaded }}/{{ $total }}</span>
              </div>
            </div>
            <div>
              <p class="font-body text-sm font-medium text-on-surface">{{ __('Document Status') }}</p>
              <p class="font-body text-sm text-on-surface-variant">
                {{ __(':count documents remaining', ['count' => $total - $uploaded]) }}</p>
            </div>
          </div>
        </div>

        {{-- Support Card --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-outline-variant/30">
          <div class="w-10 h-10 rounded-full bg-gold-pale flex items-center justify-center mb-4 text-gold-dark">
            <span class="material-symbols-outlined">support_agent</span>
          </div>
          <h3 class="font-heading text-base font-semibold text-on-surface mb-1">{{ __('Need Assistance?') }}</h3>
          <p class="font-body text-sm text-on-surface-variant mb-4">
            {{ __('Our admission team is here to help you through the process.') }}</p>
          <div class="space-y-1">
            <a class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-container-lowest transition-colors"
              href="mailto:admissions@stellagracia.sch.id">
              <span class="material-symbols-outlined text-[18px] text-outline">mail</span>
              <span class="font-body text-sm text-on-surface">admissions@stellagracia.sch.id</span>
            </a>
            <a class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-container-lowest transition-colors"
              href="tel:+628112345678">
              <span class="material-symbols-outlined text-[18px] text-outline">call</span>
              <span class="font-body text-sm text-on-surface">+62 811 2345 678</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
