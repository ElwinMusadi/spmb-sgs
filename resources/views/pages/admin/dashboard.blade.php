<?php

use App\Models\Application;
use App\Models\SchoolLevel;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Admin Overview')] class extends Component {
    /**
     * Estimated registration fee per admitted/paid applicant (IDR).
     */
    private const REGISTRATION_FEE = 754500;

    /**
     * Guard the admin dashboard against non-admin users.
     */
    public function mount(): mixed
    {
        if (! Auth::user()->isSuperAdmin()) {
            return $this->redirectRoute('dashboard', navigate: true);
        }

        return null;
    }

    /**
     * Total number of applications submitted.
     */
    #[Computed]
    public function totalApplicants(): int
    {
        return Application::count();
    }

    /**
     * Applications waiting for staff review (submitted but not yet decided).
     */
    #[Computed]
    public function pendingReview(): int
    {
        return Application::where('status', 'submitted')->count();
    }

    /**
     * Applications that have been admitted.
     */
    #[Computed]
    public function admitted(): int
    {
        return Application::where('status', 'admitted')->count();
    }

    /**
     * Applications still in draft (incomplete).
     */
    #[Computed]
    public function drafts(): int
    {
        return Application::where('status', 'draft')->count();
    }

    /**
     * Number of applications with a settled registration payment.
     */
    #[Computed]
    public function paidCount(): int
    {
        return Application::where('payment_status', 'paid')->count();
    }

    /**
     * Estimated registration revenue based on settled payments.
     */
    #[Computed]
    public function estimatedRevenue(): int
    {
        return $this->paidCount * self::REGISTRATION_FEE;
    }

    /**
     * Applicant counts grouped by school level, for the bar chart.
     *
     * @return array<int, array{code: string, name: string, count: int}>
     */
    #[Computed]
    public function applicationsByLevel(): array
    {
        $counts = Application::selectRaw('school_level_id, COUNT(*) as aggregate')
            ->groupBy('school_level_id')
            ->pluck('aggregate', 'school_level_id');

        return SchoolLevel::orderBy('id')
            ->get()
            ->map(fn (SchoolLevel $level): array => [
                'code' => $level->code,
                'name' => $level->name,
                'count' => (int) ($counts[$level->id] ?? 0),
            ])
            ->all();
    }

    /**
     * The highest single-level applicant count (chart scaling helper).
     */
    #[Computed]
    public function maxLevelCount(): int
    {
        $counts = array_column($this->applicationsByLevel, 'count');

        return max(1, ...($counts ?: [0]));
    }

    /**
     * Status distribution percentages for the donut chart.
     *
     * @return array<string, array{count: int, percentage: int}>
     */
    #[Computed]
    public function statusDistribution(): array
    {
        $total = max(1, $this->totalApplicants);

        $admitted = $this->admitted;
        $pending = $this->pendingReview;
        $drafts = $this->drafts;

        return [
            'admitted' => [
                'count' => $admitted,
                'percentage' => (int) round(($admitted / $total) * 100),
            ],
            'pending' => [
                'count' => $pending,
                'percentage' => (int) round(($pending / $total) * 100),
            ],
            'drafts' => [
                'count' => $drafts,
                'percentage' => (int) round(($drafts / $total) * 100),
            ],
        ];
    }

    /**
     * Recent applications for the activity feed.
     *
     * @return \Illuminate\Support\Collection<int, Application>
     */
    #[Computed]
    public function recentApplications()
    {
        return Application::with('schoolLevel')
            ->latest('updated_at')
            ->limit(6)
            ->get();
    }

    /**
     * Format a rupiah amount into a compact human-readable string.
     */
    public function formatCompactRupiah(int $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000_000, 1, ',', '.'), '0'), ',').' M';
        }

        if ($amount >= 1_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000, 1, ',', '.'), '0'), ',').' jt';
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}; ?>

<div class="flex flex-col w-full max-w-7xl mx-auto gap-8 pb-16">

  {{-- Header --}}
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div class="flex flex-col gap-1">
      <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-navy text-[32px]">insights</span>
        <h1 class="font-heading text-3xl font-semibold text-navy">{{ __('Admin Overview') }}</h1>
      </div>
      <p class="font-body text-base text-on-surface-variant">
        {{ __('Real-time admission metrics for the Student Admission System.') }}</p>
    </div>
    <a href="{{ route('home') }}"
      class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-navy text-white font-body text-sm font-semibold hover:bg-navy-dark transition-colors shadow-md">
      <span class="material-symbols-outlined text-[18px]">public</span>
      {{ __('View Public Site') }}
    </a>
  </div>

  {{-- Key Metrics --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
    {{-- Total Applicants --}}
    <div
      class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-navy relative overflow-hidden group hover:shadow-md transition-all">
      <div class="absolute -right-4 -top-4 w-24 h-24 bg-royal-pale/40 rounded-full blur-2xl"></div>
      <div class="flex justify-between items-start relative z-10">
        <div>
          <p class="font-body text-xs text-on-surface-variant uppercase tracking-wider">{{ __('Total Applicants') }}
          </p>
          <h3 class="font-heading text-4xl font-bold text-navy mt-2">{{ $this->totalApplicants }}</h3>
        </div>
        <div class="w-10 h-10 rounded-full bg-navy/10 flex items-center justify-center text-navy">
          <span class="material-symbols-outlined">groups</span>
        </div>
      </div>
      <div class="flex items-center gap-1.5 mt-4 relative z-10">
        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">calendar_today</span>
        <span class="font-body text-xs text-on-surface-variant">{{ __('All academic years') }}</span>
      </div>
    </div>

    {{-- Pending Review --}}
    <div
      class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-gold relative overflow-hidden group hover:shadow-md transition-all">
      <div class="absolute -right-4 -top-4 w-24 h-24 bg-gold-pale/40 rounded-full blur-2xl"></div>
      <div class="flex justify-between items-start relative z-10">
        <div>
          <p class="font-body text-xs text-on-surface-variant uppercase tracking-wider">{{ __('Pending Review') }}</p>
          <h3 class="font-heading text-4xl font-bold text-on-surface mt-2">{{ $this->pendingReview }}</h3>
        </div>
        <div class="w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center text-gold-dark">
          <span class="material-symbols-outlined">pending_actions</span>
        </div>
      </div>
      <div class="flex items-center gap-1.5 mt-4 relative z-10">
        <span class="material-symbols-outlined text-[16px] text-gold-dark">priority_high</span>
        <span class="font-body text-xs text-on-surface-variant">{{ __('Awaiting staff decision') }}</span>
      </div>
    </div>

    {{-- Admitted --}}
    <div
      class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-600 relative overflow-hidden group hover:shadow-md transition-all">
      <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-100 rounded-full blur-2xl"></div>
      <div class="flex justify-between items-start relative z-10">
        <div>
          <p class="font-body text-xs text-on-surface-variant uppercase tracking-wider">{{ __('Admitted') }}</p>
          <h3 class="font-heading text-4xl font-bold text-on-surface mt-2">{{ $this->admitted }}</h3>
        </div>
        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
          <span class="material-symbols-outlined">check_circle</span>
        </div>
      </div>
      @php($admitPct = $this->totalApplicants > 0 ? (int) round(($this->admitted / $this->totalApplicants) * 100) : 0)
      <div class="w-full bg-surface-container h-2 rounded-full mt-4 overflow-hidden relative z-10">
        <div class="bg-green-600 h-full rounded-full" style="width: {{ $admitPct }}%;"></div>
      </div>
      <p class="font-body text-xs text-on-surface-variant mt-1.5 relative z-10">
        {{ __(':pct% of total applicants', ['pct' => $admitPct]) }}</p>
    </div>

    {{-- Estimated Revenue --}}
    <div
      class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-royal relative overflow-hidden group hover:shadow-md transition-all">
      <div class="absolute -right-4 -top-4 w-24 h-24 bg-royal-pale/40 rounded-full blur-2xl"></div>
      <div class="flex justify-between items-start relative z-10">
        <div>
          <p class="font-body text-xs text-on-surface-variant uppercase tracking-wider">{{ __('Reg. Revenue (Est)') }}
          </p>
          <h3 class="font-heading text-2xl font-bold text-on-surface mt-2">
            {{ $this->formatCompactRupiah($this->estimatedRevenue) }}</h3>
        </div>
        <div class="w-10 h-10 rounded-full bg-royal/10 flex items-center justify-center text-royal">
          <span class="material-symbols-outlined">account_balance_wallet</span>
        </div>
      </div>
      <div class="flex items-center gap-1.5 mt-4 relative z-10">
        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">receipt_long</span>
        <span class="font-body text-xs text-on-surface-variant">
          {{ __(':count paid registrations', ['count' => $this->paidCount]) }}</span>
      </div>
    </div>
  </div>

  {{-- Charts --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Applications by Level --}}
    <div class="bg-white rounded-xl p-6 shadow-sm border border-outline-variant/30 lg:col-span-2 flex flex-col">
      <div class="flex justify-between items-center mb-6">
        <h2 class="font-heading text-lg font-semibold text-navy">{{ __('Applications by Grade Level') }}</h2>
      </div>

      <div class="flex-1 flex items-end gap-4 pt-6 relative min-h-[250px]">
        @forelse ($this->applicationsByLevel as $level)
          @php($heightPct = (int) round(($level['count'] / $this->maxLevelCount) * 100))
          <div class="flex-1 flex flex-col items-center gap-3 group relative">
            <div class="w-full flex items-end justify-center h-[200px]">
              <div
                class="w-full max-w-[56px] bg-navy rounded-t-md relative group-hover:bg-navy-dark transition-all duration-500"
                style="height: {{ max($level['count'] > 0 ? 6 : 2, $heightPct) }}%;">
                <div
                  class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-navy text-white text-xs font-semibold py-1 px-2 rounded shadow-md transition-opacity whitespace-nowrap">
                  {{ $level['count'] }} {{ __('applicants') }}</div>
              </div>
            </div>
            <span class="font-body text-sm font-semibold text-on-surface-variant">{{ $level['code'] }}</span>
          </div>
        @empty
          <div class="flex-1 flex flex-col items-center justify-center text-on-surface-variant py-12">
            <span class="material-symbols-outlined text-[40px] mb-2 opacity-50">bar_chart</span>
            <p class="font-body text-sm">{{ __('No applications yet.') }}</p>
          </div>
        @endforelse
      </div>
    </div>

    {{-- Status Overview Donut --}}
    <div class="bg-white rounded-xl p-6 shadow-sm border border-outline-variant/30 flex flex-col">
      <h2 class="font-heading text-lg font-semibold text-navy mb-4">{{ __('Status Overview') }}</h2>

      @php($dist = $this->statusDistribution)
      @php($admittedPct = $dist['admitted']['percentage'])
      @php($pendingPct = $dist['pending']['percentage'])
      @php($draftPct = $dist['drafts']['percentage'])
      @php($circ = 251.2)
      <div class="flex-1 flex flex-col items-center justify-center relative">
        <svg class="w-44 h-44 transform -rotate-90" viewBox="0 0 100 100">
          <circle cx="50" cy="50" fill="transparent" r="40" stroke="#edeeef" stroke-width="18"></circle>
          {{-- Admitted (navy) --}}
          <circle cx="50" cy="50" fill="transparent" r="40" stroke="#243070" stroke-width="18"
            stroke-dasharray="{{ round($circ * $admittedPct / 100, 1) }} {{ $circ }}" stroke-dashoffset="0"></circle>
          {{-- Pending (gold) --}}
          <circle cx="50" cy="50" fill="transparent" r="40" stroke="#bd9c4f" stroke-width="18"
            stroke-dasharray="{{ round($circ * $pendingPct / 100, 1) }} {{ $circ }}"
            stroke-dashoffset="{{ round(-$circ * $admittedPct / 100, 1) }}"></circle>
          {{-- Drafts (royal-pale) --}}
          <circle cx="50" cy="50" fill="transparent" r="40" stroke="#bac3ff" stroke-width="18"
            stroke-dasharray="{{ round($circ * $draftPct / 100, 1) }} {{ $circ }}"
            stroke-dashoffset="{{ round(-$circ * ($admittedPct + $pendingPct) / 100, 1) }}"></circle>
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          <span class="font-heading text-3xl font-bold text-navy">{{ $this->totalApplicants }}</span>
          <span class="font-body text-xs text-on-surface-variant">{{ __('Total') }}</span>
        </div>
      </div>

      <div class="mt-4 flex flex-col gap-2.5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-sm bg-navy"></div>
            <span class="font-body text-sm text-on-surface">{{ __('Admitted') }}</span>
          </div>
          <span class="font-body text-sm font-semibold text-on-surface">{{ $admittedPct }}%</span>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-sm bg-gold"></div>
            <span class="font-body text-sm text-on-surface">{{ __('Pending Review') }}</span>
          </div>
          <span class="font-body text-sm font-semibold text-on-surface">{{ $pendingPct }}%</span>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-sm bg-royal-dim"></div>
            <span class="font-body text-sm text-on-surface">{{ __('Incomplete Drafts') }}</span>
          </div>
          <span class="font-body text-sm font-semibold text-on-surface">{{ $draftPct }}%</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Recent Activity & Quick Actions --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Recent Applications --}}
    <div class="bg-white rounded-xl p-6 shadow-sm border border-outline-variant/30 lg:col-span-2">
      <div class="flex justify-between items-center mb-6 border-b border-outline-variant/30 pb-3">
        <h2 class="font-heading text-lg font-semibold text-navy">{{ __('Recent Applications') }}</h2>
      </div>

      <div class="flex flex-col gap-3">
        @forelse ($this->recentApplications as $application)
          <div
            class="flex items-start gap-4 group p-2.5 rounded-lg hover:bg-surface-container-lowest transition-colors">
            @php($status = $application->status)
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
              @if ($application->payment_status === 'paid') bg-green-100 text-green-600
              @elseif ($status === 'submitted') bg-gold-pale text-gold-dark
              @else bg-royal-pale text-navy @endif">
              <span class="material-symbols-outlined text-[20px]">
                @if ($application->payment_status === 'paid') payments
                @elseif ($status === 'submitted') task_alt
                @else edit_document @endif
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start gap-3">
                <p class="font-body text-sm text-on-surface">
                  <span class="font-semibold">{{ $application->student_full_name ?? __('Unnamed applicant') }}</span>
                  @if ($application->schoolLevel)
                    <span class="text-on-surface-variant">({{ $application->schoolLevel->code }})</span>
                  @endif
                </p>
                <span class="font-body text-xs text-on-surface-variant whitespace-nowrap">
                  {{ $application->updated_at?->diffForHumans() }}</span>
              </div>
              <div class="flex items-center gap-2 mt-1">
                @if ($application->payment_status === 'paid')
                  <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-semibold uppercase">
                    {{ __('Paid') }}</span>
                @endif
                <span
                  class="inline-flex items-center gap-1 px-2 py-0.5 bg-surface-container text-on-surface-variant rounded text-[10px] font-semibold uppercase">
                  {{ __(ucfirst($status)) }}</span>
              </div>
            </div>
          </div>
        @empty
          <div class="flex flex-col items-center justify-center text-on-surface-variant py-12">
            <span class="material-symbols-outlined text-[40px] mb-2 opacity-50">inbox</span>
            <p class="font-body text-sm">{{ __('No recent activity.') }}</p>
          </div>
        @endforelse
      </div>
    </div>

    {{-- Alerts & Quick Actions --}}
    <div class="flex flex-col gap-6">
      {{-- Pending Review Alert --}}
      @if ($this->pendingReview > 0)
        <div class="bg-gold-pale/40 rounded-xl p-5 border border-gold/30 flex flex-col gap-2">
          <div class="flex items-center gap-2 text-gold-dark">
            <span class="material-symbols-outlined">warning</span>
            <h3 class="font-heading text-sm font-semibold">{{ __('Review Backlog') }}</h3>
          </div>
          <p class="font-body text-sm text-on-surface-variant">
            {{ __(':count applications are waiting for review. Process them to keep the pipeline moving.', ['count' => $this->pendingReview]) }}
          </p>
        </div>
      @endif

      {{-- Quick Actions --}}
      <div class="bg-white rounded-xl p-5 shadow-sm border border-outline-variant/30 flex-1">
        <h3 class="font-heading text-base font-semibold text-navy mb-4">{{ __('Quick Actions') }}</h3>
        <div class="flex flex-col gap-2">
          <a href="{{ route('application') }}"
            class="flex items-center justify-between p-2.5 rounded-lg hover:bg-surface-container-lowest transition-colors group">
            <div class="flex items-center gap-3">
              <div
                class="w-8 h-8 rounded bg-surface-container flex items-center justify-center text-on-surface-variant group-hover:text-navy transition-colors">
                <span class="material-symbols-outlined text-[18px]">list_alt</span>
              </div>
              <span class="font-body text-sm text-on-surface">{{ __('Review Applications') }}</span>
            </div>
            <span
              class="material-symbols-outlined text-outline-variant group-hover:text-navy group-hover:translate-x-1 transition-all">chevron_right</span>
          </a>
          <a href="{{ route('home') }}#kontak"
            class="flex items-center justify-between p-2.5 rounded-lg hover:bg-surface-container-lowest transition-colors group">
            <div class="flex items-center gap-3">
              <div
                class="w-8 h-8 rounded bg-surface-container flex items-center justify-center text-on-surface-variant group-hover:text-navy transition-colors">
                <span class="material-symbols-outlined text-[18px]">campaign</span>
              </div>
              <span class="font-body text-sm text-on-surface">{{ __('Manage Announcements') }}</span>
            </div>
            <span
              class="material-symbols-outlined text-outline-variant group-hover:text-navy group-hover:translate-x-1 transition-all">chevron_right</span>
          </a>
          <a href="{{ route('profile.edit') }}"
            class="flex items-center justify-between p-2.5 rounded-lg hover:bg-surface-container-lowest transition-colors group">
            <div class="flex items-center gap-3">
              <div
                class="w-8 h-8 rounded bg-surface-container flex items-center justify-center text-on-surface-variant group-hover:text-navy transition-colors">
                <span class="material-symbols-outlined text-[18px]">settings</span>
              </div>
              <span class="font-body text-sm text-on-surface">{{ __('Admission Settings') }}</span>
            </div>
            <span
              class="material-symbols-outlined text-outline-variant group-hover:text-navy group-hover:translate-x-1 transition-all">chevron_right</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
