<?php

use App\Models\Application;
use App\Models\SchoolLevel;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Applicants')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $levelFilter = '';

    // Detail drawer state
    public bool $showDetail = false;
    public ?int $selectedId = null;

    /**
     * Guard the page against users without applicant view permission.
     */
    public function mount(): mixed
    {
        if (! Auth::user()->can('applicants.view')) {
            return $this->redirectRoute('dashboard', navigate: true);
        }

        return null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedLevelFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Available school levels for the filter.
     *
     * @return \Illuminate\Support\Collection<int, SchoolLevel>
     */
    #[Computed]
    public function levels()
    {
        return SchoolLevel::orderBy('id')->get();
    }

    /**
     * Paginated, filtered applicant list.
     */
    #[Computed]
    public function applicants()
    {
        return Application::with(['schoolLevel', 'academicYear', 'user'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($sub): void {
                    $sub->where('student_full_name', 'like', "%{$this->search}%")
                        ->orWhere('student_nisn', 'like', "%{$this->search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->levelFilter !== '', fn ($query) => $query->where('school_level_id', $this->levelFilter))
            ->latest('updated_at')
            ->paginate(10);
    }

    /**
     * Summary counts for the status chips.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function statusCounts(): array
    {
        $counts = Application::selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'all' => Application::count(),
            'draft' => (int) ($counts['draft'] ?? 0),
            'submitted' => (int) ($counts['submitted'] ?? 0),
            'admitted' => (int) ($counts['admitted'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
        ];
    }

    /**
     * The currently selected application for the detail drawer.
     */
    #[Computed]
    public function selectedApplication(): ?Application
    {
        if (! $this->selectedId) {
            return null;
        }

        return Application::with(['schoolLevel', 'academicYear', 'user', 'documents'])
            ->find($this->selectedId);
    }

    /**
     * Open the detail drawer for an application.
     */
    public function viewApplicant(int $id): void
    {
        $this->selectedId = $id;
        $this->showDetail = true;
    }

    /**
     * Admit an applicant (requires review permission).
     */
    public function admit(int $id): void
    {
        $this->authorizeReview();
        $this->applyStatus($id, 'admitted', __('Applicant admitted.'));
    }

    /**
     * Reject an applicant (requires review permission).
     */
    public function reject(int $id): void
    {
        $this->authorizeReview();
        $this->applyStatus($id, 'rejected', __('Applicant rejected.'));
    }

    /**
     * Return an admitted/rejected applicant back to submitted review state.
     */
    public function resetReview(int $id): void
    {
        $this->authorizeReview();
        $this->applyStatus($id, 'submitted', __('Applicant returned to review.'));
    }

    /**
     * Verify a registration payment (requires payments.verify permission).
     */
    public function verifyPayment(int $id): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);

        $application = Application::findOrFail($id);
        $application->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        unset($this->applicants);
        Flux::toast(variant: 'success', text: __('Payment marked as verified.'));
    }

    /**
     * Apply a status transition to an application.
     */
    protected function applyStatus(int $id, string $status, string $message): void
    {
        $application = Application::findOrFail($id);
        $application->update(['status' => $status]);

        unset($this->applicants);
        Flux::toast(variant: 'success', text: $message);
    }

    /**
     * Ensure the current user can review applications.
     */
    private function authorizeReview(): void
    {
        abort_unless(Auth::user()->can('applicants.review'), 403);
    }

    /**
     * Whether the current user may review applications.
     */
    #[Computed]
    public function canReview(): bool
    {
        return Auth::user()->can('applicants.review');
    }

    /**
     * Whether the current user may verify payments.
     */
    #[Computed]
    public function canVerifyPayment(): bool
    {
        return Auth::user()->can('payments.verify');
    }

    /**
     * Whether the current user may view documents.
     */
    #[Computed]
    public function canViewDocuments(): bool
    {
        return Auth::user()->can('documents.view');
    }

    /**
     * Resolve a stored document URL.
     */
    public function documentUrl(string $path): string
    {
        return Storage::url($path);
    }

    /**
     * Display metadata for a status badge.
     *
     * @return array{label: string, classes: string, dot: string}
     */
    public function statusBadge(string $status): array
    {
        return match ($status) {
            'admitted' => ['label' => __('Admitted'), 'classes' => 'bg-green-100 text-green-700', 'dot' => 'bg-green-500'],
            'rejected' => ['label' => __('Rejected'), 'classes' => 'bg-red-100 text-red-700', 'dot' => 'bg-red-500'],
            'submitted' => ['label' => __('Submitted'), 'classes' => 'bg-gold-pale text-gold-dark', 'dot' => 'bg-gold'],
            default => ['label' => __('Draft'), 'classes' => 'bg-surface-container-high text-on-surface-variant', 'dot' => 'bg-outline'],
        };
    }
}; ?>

<div class="flex flex-col w-full max-w-7xl mx-auto gap-8 pb-16">

  {{-- Header --}}
  <div class="flex flex-col gap-1">
    <div class="flex items-center gap-3">
      <span class="material-symbols-outlined text-navy text-[32px]">group</span>
      <h1 class="font-heading text-3xl font-semibold text-navy">{{ __('Applicants') }}</h1>
    </div>
    <p class="font-body text-base text-on-surface-variant max-w-2xl">
      {{ __('Review, verify, and decide on student admission applications.') }}</p>
  </div>

  {{-- Status Chips --}}
  <div class="flex flex-wrap gap-2">
    @php($counts = $this->statusCounts)
    @foreach (['' => __('All'), 'draft' => __('Draft'), 'submitted' => __('Submitted'), 'admitted' => __('Admitted'), 'rejected' => __('Rejected')] as $value => $label)
      @php($key = $value === '' ? 'all' : $value)
      <button wire:click="$set('statusFilter', '{{ $value }}')"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-body text-sm font-semibold transition-colors
        {{ $statusFilter === $value ? 'bg-navy text-white' : 'bg-white border border-outline-variant/40 text-on-surface-variant hover:border-navy/50' }}">
        {{ $label }}
        <span
          class="px-1.5 py-0.5 rounded-full text-xs {{ $statusFilter === $value ? 'bg-white/20' : 'bg-surface-container' }}">
          {{ $counts[$key] ?? 0 }}</span>
      </button>
    @endforeach
  </div>

  {{-- Filters --}}
  <div
    class="bg-white p-4 rounded-xl shadow-sm border border-outline-variant/30 flex flex-col sm:flex-row justify-between items-center gap-4">
    <div class="flex items-center gap-3 w-full sm:w-auto">
      <div class="relative w-full sm:w-72">
        <span
          class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
        <input wire:model.live.debounce.300ms="search" type="text"
          placeholder="{{ __('Search name, NISN, or email...') }}"
          class="w-full bg-surface-container-lowest border border-outline-variant/50 font-body text-sm text-on-surface pl-10 pr-3 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy transition-all" />
      </div>
      <div class="relative">
        <select wire:model.live="levelFilter"
          class="appearance-none bg-surface-container-lowest border border-outline-variant/50 font-body text-sm text-on-surface-variant pl-3 pr-9 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy transition-all cursor-pointer">
          <option value="">{{ __('All Levels') }}</option>
          @foreach ($this->levels as $level)
            <option value="{{ $level->id }}">{{ $level->code }}</option>
          @endforeach
        </select>
        <span
          class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[18px]">expand_more</span>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr
            class="bg-surface-container-lowest font-body text-xs text-on-surface-variant uppercase tracking-wider border-b border-outline-variant/30">
            <th class="py-4 px-5 font-semibold">{{ __('Applicant') }}</th>
            <th class="py-4 px-5 font-semibold">{{ __('Level') }}</th>
            <th class="py-4 px-5 font-semibold">{{ __('Payment') }}</th>
            <th class="py-4 px-5 font-semibold">{{ __('Status') }}</th>
            <th class="py-4 px-5 font-semibold text-right">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="font-body text-sm text-on-surface divide-y divide-outline-variant/20">
          @forelse ($this->applicants as $applicant)
            @php($badge = $this->statusBadge($applicant->status))
            <tr class="hover:bg-surface-container-lowest transition-colors">
              <td class="py-3 px-5">
                <div class="flex flex-col min-w-0">
                  <span class="font-medium truncate">
                    {{ $applicant->student_full_name ?? __('Unnamed applicant') }}</span>
                  <span class="text-on-surface-variant text-xs truncate">
                    {{ $applicant->user?->email }}
                    @if ($applicant->student_nisn)
                      &bull; NISN {{ $applicant->student_nisn }}
                    @endif
                  </span>
                </div>
              </td>
              <td class="py-3 px-5">
                @if ($applicant->schoolLevel)
                  <span class="font-body text-sm">{{ $applicant->schoolLevel->code }}</span>
                  <span class="text-on-surface-variant text-xs">/ {{ $applicant->grade_level ?? '-' }}</span>
                @else
                  <span class="text-on-surface-variant">-</span>
                @endif
              </td>
              <td class="py-3 px-5">
                @if ($applicant->payment_status === 'paid')
                  <span class="inline-flex items-center gap-1.5 font-body text-xs text-green-700">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span> {{ __('Paid') }}
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 font-body text-xs text-on-surface-variant">
                    <span class="material-symbols-outlined text-[16px]">schedule</span> {{ __('Awaiting') }}
                  </span>
                @endif
              </td>
              <td class="py-3 px-5">
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-body text-xs font-semibold {{ $badge['classes'] }}">
                  <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span> {{ $badge['label'] }}
                </span>
              </td>
              <td class="py-3 px-5 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button wire:click="viewApplicant({{ $applicant->id }})" title="{{ __('View Details') }}"
                    class="w-8 h-8 rounded-full hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-navy transition-colors">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>
                  @if ($this->canReview && $applicant->status === 'submitted')
                    <button wire:click="admit({{ $applicant->id }})" title="{{ __('Admit') }}"
                      class="w-8 h-8 rounded-full hover:bg-green-100 flex items-center justify-center text-on-surface-variant hover:text-green-600 transition-colors">
                      <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    </button>
                    <button wire:click="reject({{ $applicant->id }})" title="{{ __('Reject') }}"
                      class="w-8 h-8 rounded-full hover:bg-red-100 flex items-center justify-center text-on-surface-variant hover:text-red-600 transition-colors">
                      <span class="material-symbols-outlined text-[18px]">cancel</span>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-12 text-center">
                <div class="flex flex-col items-center justify-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-[40px] mb-2 opacity-50">inbox</span>
                  <p class="font-body text-sm">{{ __('No applicants found.') }}</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($this->applicants->hasPages())
      <div class="p-4 border-t border-outline-variant/30">
        {{ $this->applicants->links() }}
      </div>
    @endif
  </div>

  {{-- Detail Drawer --}}
  <flux:modal wire:model="showDetail" variant="flyout" class="min-w-[32rem]">
    @if ($this->selectedApplication)
      @php($app = $this->selectedApplication)
      @php($badge = $this->statusBadge($app->status))
      <div class="flex flex-col gap-6">
        <div>
          <flux:heading size="lg">{{ $app->student_full_name ?? __('Applicant') }}</flux:heading>
          <div class="flex items-center gap-2 mt-2">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-body text-xs font-semibold {{ $badge['classes'] }}">
              <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span> {{ $badge['label'] }}
            </span>
            @if ($app->payment_status === 'paid')
              <span
                class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-semibold uppercase">{{ __('Paid') }}</span>
            @endif
          </div>
        </div>

        {{-- Student Info --}}
        <div class="flex flex-col gap-3">
          <h3 class="font-body text-xs font-semibold text-on-surface uppercase tracking-wider">
            {{ __('Student Data') }}</h3>
          <dl class="grid grid-cols-2 gap-x-4 gap-y-3 font-body text-sm">
            <div>
              <dt class="text-on-surface-variant text-xs">{{ __('NISN') }}</dt>
              <dd class="text-on-surface">{{ $app->student_nisn ?? '-' }}</dd>
            </div>
            <div>
              <dt class="text-on-surface-variant text-xs">{{ __('NIK') }}</dt>
              <dd class="text-on-surface">{{ $app->student_nik ?? '-' }}</dd>
            </div>
            <div>
              <dt class="text-on-surface-variant text-xs">{{ __('Level / Grade') }}</dt>
              <dd class="text-on-surface">{{ $app->schoolLevel?->code ?? '-' }} / {{ $app->grade_level ?? '-' }}</dd>
            </div>
            <div>
              <dt class="text-on-surface-variant text-xs">{{ __('Academic Year') }}</dt>
              <dd class="text-on-surface">{{ $app->academicYear?->name ?? '-' }}</dd>
            </div>
            <div>
              <dt class="text-on-surface-variant text-xs">{{ __('Birth') }}</dt>
              <dd class="text-on-surface">{{ $app->student_birth_place ?? '-' }},
                {{ $app->student_birth_date?->format('d M Y') ?? '-' }}</dd>
            </div>
            <div>
              <dt class="text-on-surface-variant text-xs">{{ __('Gender') }}</dt>
              <dd class="text-on-surface">{{ ucfirst($app->student_gender ?? '-') }}</dd>
            </div>
          </dl>
        </div>

        {{-- Parents --}}
        <div class="flex flex-col gap-3">
          <h3 class="font-body text-xs font-semibold text-on-surface uppercase tracking-wider">
            {{ __('Parent Contact') }}</h3>
          <dl class="grid grid-cols-2 gap-x-4 gap-y-3 font-body text-sm">
            <div>
              <dt class="text-on-surface-variant text-xs">{{ __('Father') }}</dt>
              <dd class="text-on-surface">{{ $app->father_name ?? '-' }}</dd>
              <dd class="text-on-surface-variant text-xs">{{ $app->father_phone ?? '' }}</dd>
            </div>
            <div>
              <dt class="text-on-surface-variant text-xs">{{ __('Mother') }}</dt>
              <dd class="text-on-surface">{{ $app->mother_name ?? '-' }}</dd>
              <dd class="text-on-surface-variant text-xs">{{ $app->mother_phone ?? '' }}</dd>
            </div>
          </dl>
        </div>

        {{-- Documents --}}
        @if ($this->canViewDocuments)
          <div class="flex flex-col gap-3">
            <h3 class="font-body text-xs font-semibold text-on-surface uppercase tracking-wider">
              {{ __('Documents') }}</h3>
            @forelse ($app->documents as $doc)
              <a href="{{ $this->documentUrl($doc->file_path) }}" target="_blank"
                class="flex items-center gap-3 p-2.5 rounded-lg bg-surface-container-lowest hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-navy text-[20px]">draft</span>
                <div class="flex-1 min-w-0">
                  <p class="font-body text-sm text-on-surface truncate">{{ $doc->file_name }}</p>
                  <p class="font-body text-xs text-on-surface-variant uppercase">{{ str_replace('_', ' ', $doc->document_type) }}</p>
                </div>
                <span class="material-symbols-outlined text-on-surface-variant text-[18px]">open_in_new</span>
              </a>
            @empty
              <p class="font-body text-sm text-on-surface-variant italic">{{ __('No documents uploaded.') }}</p>
            @endforelse
          </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-col gap-3 pt-4 border-t border-outline-variant/30">
          @if ($this->canVerifyPayment && $app->payment_status !== 'paid')
            <flux:button wire:click="verifyPayment({{ $app->id }})" variant="filled" icon="banknotes"
              class="w-full">
              {{ __('Verify Payment') }}
            </flux:button>
          @endif

          @if ($this->canReview)
            @if ($app->status === 'submitted')
              <div class="flex gap-3">
                <flux:button wire:click="admit({{ $app->id }})" variant="primary" icon="check" class="flex-1">
                  {{ __('Admit') }}</flux:button>
                <flux:button wire:click="reject({{ $app->id }})" variant="danger" icon="x-mark" class="flex-1">
                  {{ __('Reject') }}</flux:button>
              </div>
            @elseif (in_array($app->status, ['admitted', 'rejected']))
              <flux:button wire:click="resetReview({{ $app->id }})" variant="ghost" icon="arrow-uturn-left"
                class="w-full">
                {{ __('Return to Review') }}</flux:button>
            @endif
          @endif
        </div>
      </div>
    @endif
  </flux:modal>
</div>
