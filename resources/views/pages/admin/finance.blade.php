<?php

use App\Models\Application;
use App\Models\PaymentAccount;
use App\Models\PaymentFee;
use App\Models\ApplicationFee;
use App\Models\AcademicYear;
use App\Models\SchoolLevel;
use App\Services\PaymentFeeService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Finance Management')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $paymentFilter = '';

    // Payment account form state
    public bool $showAccountModal = false;
    public ?int $editingAccountId = null;
    public string $bank_name = '';
    public string $account_number = '';
    public string $account_holder = '';
    public string $branch = '';
    public bool $account_is_active = true;

    public bool $showDeleteAccountModal = false;
    public ?int $deletingAccountId = null;

    // Payment fee form state
    public bool $showFeeModal = false;
    public ?int $editingFeeId = null;
    public string $fee_type = 'registration';
    public string $fee_name = '';
    public string $fee_description = '';
    public string $fee_amount = '';
    public string $fee_academic_year_id = '';
    public string $fee_school_level_id = '';
    public bool $fee_is_active = true;
    public int $fee_sort_order = 0;
    public bool $showDeleteFeeModal = false;
    public ?int $deletingFeeId = null;

    /**
     * Only Administrators and Finance staff may access this page.
     */
    public function mount(): mixed
    {
        if (! Auth::user()->can('payments.view')) {
            return $this->redirectRoute(Auth::user()->homeRoute(), navigate: true);
        }

        return null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Whether the user may verify payments.
     */
    #[Computed]
    public function canVerify(): bool
    {
        return Auth::user()->can('payments.verify');
    }

    /**
     * Total collected revenue from settled payments.
     */
    #[Computed]
    public function totalRevenue(): int
    {
        return (int) ApplicationFee::whereHas('application', fn ($query) => $query->where('payment_status', 'paid'))->sum('amount');
    }

    /**
     * Number of confirmed (paid) payments.
     */
    #[Computed]
    public function confirmedCount(): int
    {
        return Application::where('payment_status', 'paid')->count();
    }

    /**
     * Number of payments awaiting verification (submitted but unpaid).
     */
    #[Computed]
    public function awaitingCount(): int
    {
        return Application::where('payment_status', '!=', 'paid')
            ->where('status', 'submitted')
            ->count();
    }

    /**
     * Number of pending/unpaid applications overall.
     */
    #[Computed]
    public function pendingCount(): int
    {
        return Application::where('payment_status', '!=', 'paid')->count();
    }

    /**
     * The registration fee, exposed to the view.
     */
    #[Computed]
    public function fee(): int
    {
        return $this->feesForApplication(null)['total'];
    }

    /**
     * Active academic years and school levels used by fee CRUD.
     */
    #[Computed]
    public function academicYears()
    {
        return AcademicYear::orderByDesc('is_active')->orderBy('name')->get();
    }

    #[Computed]
    public function schoolLevels()
    {
        return SchoolLevel::orderBy('id')->get();
    }

    #[Computed]
    public function fees()
    {
        return PaymentFee::with(['academicYear', 'schoolLevel'])
            ->orderByDesc('is_active')
            ->orderBy('academic_year_id')
            ->orderBy('school_level_id')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Paginated, filtered payment list.
     */
    #[Computed]
    public function payments()
    {
        return Application::with(['schoolLevel', 'user', 'fees'])
            ->whereNotNull('student_full_name')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($sub): void {
                    $sub->where('student_full_name', 'like', "%{$this->search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->paymentFilter === 'paid', fn ($query) => $query->where('payment_status', 'paid'))
            ->when($this->paymentFilter === 'awaiting', fn ($query) => $query->where('payment_status', '!=', 'paid')->where('status', 'submitted'))
            ->when($this->paymentFilter === 'pending', fn ($query) => $query->where('payment_status', '!=', 'paid')->where('status', '!=', 'submitted'))
            ->latest('updated_at')
            ->paginate(10);
    }

    /**
     * Return totals for an application or the currently configured default fees.
     *
     * @return array{subtotal: int, admin_fee: int, total: int}
     */
    private function feesForApplication(?Application $application): array
    {
        if ($application) {
            return app(PaymentFeeService::class)->totals(app(PaymentFeeService::class)->feesFor($application));
        }

        return app(PaymentFeeService::class)->totals(
            PaymentFee::where('is_active', true)->orderBy('sort_order')->get(),
        );
    }

    /**
     * The payment destination accounts.
     *
     * @return \Illuminate\Support\Collection<int, PaymentAccount>
     */
    #[Computed]
    public function accounts()
    {
        return PaymentAccount::orderBy('bank_name')->get();
    }

    /**
     * Verify a registration payment.
     */
    public function verifyPayment(int $id): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);

        $application = Application::findOrFail($id);
        $application->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        unset($this->payments);
        Flux::toast(variant: 'success', text: __('Payment verified successfully.'));
    }

    /**
     * Open the fee creation form.
     */
    public function createFee(): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);
        $this->resetFeeForm();
        $this->showFeeModal = true;
    }

    public function editFee(int $id): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);
        $fee = PaymentFee::findOrFail($id);
        $this->editingFeeId = $fee->id;
        $this->fee_type = $fee->fee_type;
        $this->fee_name = $fee->name;
        $this->fee_description = $fee->description ?? '';
        $this->fee_amount = (string) $fee->amount;
        $this->fee_academic_year_id = (string) ($fee->academic_year_id ?? '');
        $this->fee_school_level_id = (string) ($fee->school_level_id ?? '');
        $this->fee_is_active = $fee->is_active;
        $this->fee_sort_order = $fee->sort_order;
        $this->resetErrorBag();
        $this->showFeeModal = true;
    }

    public function saveFee(): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);

        $validated = $this->validate([
            'fee_type' => ['required', 'in:registration,assessment,admin'],
            'fee_name' => ['required', 'string', 'max:255'],
            'fee_description' => ['nullable', 'string', 'max:1000'],
            'fee_amount' => ['required', 'integer', 'min:0'],
            'fee_academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'fee_school_level_id' => ['nullable', 'exists:school_levels,id'],
            'fee_is_active' => ['boolean'],
            'fee_sort_order' => ['integer', 'min:0', 'max:999'],
        ]);

        $academicYearId = $validated['fee_academic_year_id'] ?: null;
        $schoolLevelId = $validated['fee_school_level_id'] ?: null;

        if ($validated['fee_type'] === 'admin') {
            $academicYearId = null;
            $schoolLevelId = null;
        }

        if ($validated['fee_type'] !== 'admin' && (! $academicYearId || ! $schoolLevelId)) {
            $this->addError('fee_academic_year_id', __('Registration and assessment fees require an academic year and school level.'));

            return;
        }

        $duplicate = PaymentFee::query()
            ->where('fee_type', $validated['fee_type'])
            ->where('academic_year_id', $academicYearId)
            ->where('school_level_id', $schoolLevelId)
            ->when($this->editingFeeId, fn ($query) => $query->where('id', '!=', $this->editingFeeId))
            ->exists();

        if ($duplicate) {
            $this->addError('fee_type', __('A fee with this type and scope already exists.'));

            return;
        }

        PaymentFee::updateOrCreate(
            ['id' => $this->editingFeeId],
            [
                'fee_type' => $validated['fee_type'],
                'name' => $validated['fee_name'],
                'description' => $validated['fee_description'] ?: null,
                'amount' => $validated['fee_amount'],
                'academic_year_id' => $academicYearId,
                'school_level_id' => $schoolLevelId,
                'is_active' => $validated['fee_is_active'],
                'sort_order' => $validated['fee_sort_order'],
            ],
        );

        $this->showFeeModal = false;
        $this->resetFeeForm();
        unset($this->fees);
        Flux::toast(variant: 'success', text: __('Payment fee saved.'));
    }

    public function toggleFee(int $id): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);
        $fee = PaymentFee::findOrFail($id);
        $fee->update(['is_active' => ! $fee->is_active]);
        unset($this->fees);
    }

    public function confirmDeleteFee(int $id): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);
        $this->deletingFeeId = $id;
        $this->showDeleteFeeModal = true;
    }

    public function deleteFee(): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);
        $fee = PaymentFee::findOrFail($this->deletingFeeId);

        if ($fee->applicationFees()->exists()) {
            $fee->update(['is_active' => false]);
            Flux::toast(variant: 'warning', text: __('This fee is used by an invoice and was deactivated instead.'));
        } else {
            $fee->delete();
            Flux::toast(variant: 'success', text: __('Payment fee deleted.'));
        }

        $this->showDeleteFeeModal = false;
        $this->deletingFeeId = null;
        unset($this->fees);
    }

    private function resetFeeForm(): void
    {
        $this->editingFeeId = null;
        $this->fee_type = 'registration';
        $this->fee_name = 'Registration Fee';
        $this->fee_description = '';
        $this->fee_amount = '';
        $this->fee_academic_year_id = '';
        $this->fee_school_level_id = '';
        $this->fee_is_active = true;
        $this->fee_sort_order = 10;
        $this->resetErrorBag();
    }

    /**
     * Build an invoice number for an application.
     */
    public function invoiceNumber(Application $application): string
    {
        return sprintf(
            'INV-SGS-%s-%s',
            $application->created_at?->format('Y') ?? now()->format('Y'),
            str_pad((string) $application->id, 4, '0', STR_PAD_LEFT),
        );
    }

    public function paymentTotal(Application $application): int
    {
        if ($application->fees->isNotEmpty()) {
            return app(PaymentFeeService::class)->totals($application->fees)['total'];
        }

        return $this->feesForApplication($application)['total'];
    }

    // --- Payment Account CRUD (requires payments.verify) ---

    public function createAccount(): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);

        $this->resetAccountForm();
        $this->showAccountModal = true;
    }

    public function editAccount(int $id): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);

        $account = PaymentAccount::findOrFail($id);
        $this->editingAccountId = $account->id;
        $this->bank_name = $account->bank_name;
        $this->account_number = $account->account_number;
        $this->account_holder = $account->account_holder;
        $this->branch = $account->branch ?? '';
        $this->account_is_active = $account->is_active;
        $this->resetErrorBag();
        $this->showAccountModal = true;
    }

    public function saveAccount(): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);

        $validated = $this->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => [
                'required', 'string', 'max:255',
                Rule::unique('payment_accounts', 'account_number')->ignore($this->editingAccountId),
            ],
            'account_holder' => ['required', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'account_is_active' => ['boolean'],
        ]);

        PaymentAccount::updateOrCreate(
            ['id' => $this->editingAccountId],
            [
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_holder' => $validated['account_holder'],
                'branch' => $validated['branch'] ?? null,
                'is_active' => $validated['account_is_active'],
            ],
        );

        $this->showAccountModal = false;
        $this->resetAccountForm();
        unset($this->accounts);
        Flux::toast(variant: 'success', text: __('Payment account saved.'));
    }

    public function confirmDeleteAccount(int $id): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);

        $this->deletingAccountId = $id;
        $this->showDeleteAccountModal = true;
    }

    public function deleteAccount(): void
    {
        abort_unless(Auth::user()->can('payments.verify'), 403);

        if ($this->deletingAccountId) {
            PaymentAccount::whereKey($this->deletingAccountId)->delete();
            unset($this->accounts);
            Flux::toast(variant: 'success', text: __('Payment account deleted.'));
        }

        $this->showDeleteAccountModal = false;
        $this->deletingAccountId = null;
    }

    private function resetAccountForm(): void
    {
        $this->editingAccountId = null;
        $this->bank_name = '';
        $this->account_number = '';
        $this->account_holder = 'Yayasan Pendidikan Stella Gracia';
        $this->branch = '';
        $this->account_is_active = true;
        $this->resetErrorBag();
    }
}; ?>

<div class="flex flex-col w-full max-w-7xl mx-auto gap-8 pb-16">

  {{-- Header --}}
  <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div class="flex flex-col gap-1">
      <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-navy text-[32px]">account_balance</span>
        <h1 class="font-heading text-3xl font-semibold text-navy">{{ __('Finance Management') }}</h1>
      </div>
      <p class="font-body text-base text-on-surface-variant">
        {{ __('Overview and management of application payments.') }}</p>
    </div>
  </div>

  {{-- Metric Cards --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
    {{-- Total Revenue --}}
    <div class="bg-navy text-white rounded-xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
      <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
      <div class="flex items-center justify-between mb-2 relative z-10">
        <span class="font-body text-sm text-white/80">{{ __('Total Revenue') }}</span>
        <span class="material-symbols-outlined text-white/80">account_balance_wallet</span>
      </div>
      <div class="font-heading text-2xl font-bold relative z-10">Rp {{ number_format($this->totalRevenue, 0, ',', '.') }}</div>
      <div class="font-body text-xs mt-3 relative z-10 text-white/80">
        {{ __('Collected from verified payments') }}</div>
    </div>

    {{-- Confirmed --}}
    <div class="bg-white rounded-xl p-5 shadow-sm relative overflow-hidden border-l-4 border-navy">
      <div class="flex items-center justify-between mb-2">
        <span class="font-body text-sm text-on-surface-variant">{{ __('Confirmed Payments') }}</span>
        <div class="w-8 h-8 rounded-full bg-navy/10 flex items-center justify-center">
          <span class="material-symbols-outlined text-navy text-[18px]">check_circle</span>
        </div>
      </div>
      <div class="font-heading text-2xl font-bold text-on-surface">{{ $this->confirmedCount }}</div>
      <div class="font-body text-xs mt-3 text-on-surface-variant">
        Rp {{ number_format($this->totalRevenue, 0, ',', '.') }} {{ __('collected') }}</div>
    </div>

    {{-- Awaiting Verification --}}
    <div class="bg-white rounded-xl p-5 shadow-sm relative overflow-hidden border-l-4 border-gold">
      <div class="flex items-center justify-between mb-2">
        <span class="font-body text-sm text-on-surface-variant">{{ __('Awaiting Verification') }}</span>
        <div class="w-8 h-8 rounded-full bg-gold/10 flex items-center justify-center">
          <span class="material-symbols-outlined text-gold-dark text-[18px]">pending_actions</span>
        </div>
      </div>
      <div class="font-heading text-2xl font-bold text-on-surface">{{ $this->awaitingCount }}</div>
      <div class="font-body text-xs mt-3 text-on-surface-variant">{{ __('Requires manual check') }}</div>
    </div>

    {{-- Pending --}}
    <div class="bg-white rounded-xl p-5 shadow-sm relative overflow-hidden border-l-4 border-red-500">
      <div class="flex items-center justify-between mb-2">
        <span class="font-body text-sm text-on-surface-variant">{{ __('Pending / Unpaid') }}</span>
        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
          <span class="material-symbols-outlined text-red-600 text-[18px]">warning</span>
        </div>
      </div>
      <div class="font-heading text-2xl font-bold text-on-surface">{{ $this->pendingCount }}</div>
      <div class="font-body text-xs mt-3 text-on-surface-variant">{{ __('Not yet settled') }}</div>
    </div>
  </div>

  {{-- Payment Status Table --}}
  <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden flex flex-col">
    <div class="p-5 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 border-b border-outline-variant/30">
      <div>
        <h2 class="font-heading text-lg font-semibold text-on-surface">{{ __('Payment Status') }}</h2>
        <p class="font-body text-sm text-on-surface-variant">{{ __('Manage and verify applicant payments.') }}</p>
      </div>
      <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
        <div class="relative w-full sm:w-64">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
          <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="{{ __('Search applicant, email...') }}"
            class="w-full bg-surface-container-lowest border border-outline-variant/50 font-body text-sm text-on-surface pl-10 pr-3 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy transition-all" />
        </div>
        <div class="flex items-center gap-1 bg-surface-container p-1 rounded-lg">
          @foreach (['' => __('All'), 'paid' => __('Paid'), 'awaiting' => __('Verify'), 'pending' => __('Pending')] as $value => $label)
            <button wire:click="$set('paymentFilter', '{{ $value }}')"
              class="px-3 py-1.5 rounded font-body text-xs font-semibold transition-colors
              {{ $paymentFilter === $value ? 'bg-white shadow-sm text-navy' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
              {{ $label }}</button>
          @endforeach
        </div>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[800px]">
        <thead>
          <tr class="bg-surface-container-lowest font-body text-xs text-on-surface-variant uppercase tracking-wider">
            <th class="p-4 font-semibold">{{ __('Applicant & Invoice') }}</th>
            <th class="p-4 font-semibold">{{ __('Grade') }}</th>
            <th class="p-4 font-semibold">{{ __('Amount') }}</th>
            <th class="p-4 font-semibold">{{ __('Status') }}</th>
            <th class="p-4 font-semibold text-right">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="font-body text-sm text-on-surface divide-y divide-outline-variant/20">
          @forelse ($this->payments as $payment)
            <tr class="hover:bg-surface-container-lowest transition-colors">
              <td class="p-4">
                <div class="font-medium text-on-surface">{{ $payment->student_full_name }}</div>
                <div class="font-mono text-xs text-on-surface-variant mt-0.5">{{ $this->invoiceNumber($payment) }}</div>
              </td>
              <td class="p-4">
                <span class="font-body text-sm">{{ $payment->schoolLevel?->code ?? '-' }}</span>
                <span class="text-on-surface-variant text-xs">{{ $payment->grade_level ?? '' }}</span>
              </td>
              <td class="p-4 font-medium tabular-nums">Rp {{ number_format($this->paymentTotal($payment), 0, ',', '.') }}</td>
              <td class="p-4">
                @if ($payment->payment_status === 'paid')
                  <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-green-100 text-green-700 font-body text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ __('Paid') }}
                  </span>
                @elseif ($payment->status === 'submitted')
                  <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gold-pale text-gold-dark font-body text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-gold"></span> {{ __('Verify') }}
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-surface-container-high text-on-surface-variant font-body text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-outline"></span> {{ __('Pending') }}
                  </span>
                @endif
              </td>
              <td class="p-4 text-right">
                @if ($payment->payment_status !== 'paid' && $this->canVerify)
                  <flux:button wire:click="verifyPayment({{ $payment->id }})" size="sm" variant="primary">
                    {{ __('Verify') }}</flux:button>
                @elseif ($payment->payment_status === 'paid')
                  <span class="material-symbols-outlined text-green-600 text-[20px]">task_alt</span>
                @else
                  <span class="font-body text-xs text-on-surface-variant italic">{{ __('View only') }}</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-12 text-center">
                <div class="flex flex-col items-center justify-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-[40px] mb-2 opacity-50">receipt_long</span>
                  <p class="font-body text-sm">{{ __('No payments found.') }}</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($this->payments->hasPages())
      <div class="p-4 border-t border-outline-variant/30">
        {{ $this->payments->links() }}
      </div>
    @endif
  </div>

  {{-- Payment Fees --}}
  <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-heading text-lg font-semibold text-on-surface">{{ __('Payment Fees') }}</h2>
        <p class="font-body text-sm text-on-surface-variant mt-1">
          {{ __('Configure fees by academic year and school level.') }}</p>
      </div>
      @if ($this->canVerify)
        <flux:button wire:click="createFee" variant="primary" icon="plus">
          {{ __('Add Fee') }}</flux:button>
      @endif
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left min-w-[850px]">
        <thead>
          <tr class="bg-surface-container-lowest font-body text-xs text-on-surface-variant uppercase tracking-wider">
            <th class="p-4 font-semibold">{{ __('Fee') }}</th>
            <th class="p-4 font-semibold">{{ __('Scope') }}</th>
            <th class="p-4 font-semibold">{{ __('Amount') }}</th>
            <th class="p-4 font-semibold">{{ __('Status') }}</th>
            <th class="p-4 font-semibold text-right">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/20 font-body text-sm">
          @forelse ($this->fees as $fee)
            <tr wire:key="payment-fee-{{ $fee->id }}" class="hover:bg-surface-container-lowest">
              <td class="p-4">
                <div class="font-semibold text-on-surface">{{ $fee->name }}</div>
                <div class="text-xs text-on-surface-variant">{{ $fee->fee_type }} · {{ $fee->description }}</div>
              </td>
              <td class="p-4 text-on-surface-variant">
                {{ $fee->academicYear?->name ?? __('Global') }}
                @if ($fee->schoolLevel)
                  · {{ $fee->schoolLevel->code }}
                @endif
              </td>
              <td class="p-4 font-semibold tabular-nums">Rp {{ number_format($fee->amount, 0, ',', '.') }}</td>
              <td class="p-4">
                <span class="inline-flex items-center gap-1.5 text-xs {{ $fee->is_active ? 'text-green-700' : 'text-red-600' }}">
                  <span class="w-2 h-2 rounded-full {{ $fee->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                  {{ $fee->is_active ? __('Active') : __('Inactive') }}
                </span>
              </td>
              <td class="p-4 text-right">
                @if ($this->canVerify)
                  <div class="flex justify-end gap-1">
                    <button wire:click="editFee({{ $fee->id }})" class="p-2 rounded hover:bg-surface-container text-on-surface-variant hover:text-navy" title="{{ __('Edit') }}">
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>
                    <button wire:click="toggleFee({{ $fee->id }})" class="p-2 rounded hover:bg-surface-container text-on-surface-variant hover:text-navy" title="{{ __('Toggle') }}">
                      <span class="material-symbols-outlined text-[18px]">{{ $fee->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                    </button>
                    <button wire:click="confirmDeleteFee({{ $fee->id }})" class="p-2 rounded hover:bg-red-100 text-on-surface-variant hover:text-red-600" title="{{ __('Delete') }}">
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </div>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-8 text-center text-sm text-on-surface-variant">{{ __('No payment fees configured.') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Payment Methods --}}
  <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="font-heading text-lg font-semibold text-on-surface">{{ __('Payment Methods') }}</h2>
        <p class="font-body text-sm text-on-surface-variant mt-1">
          {{ __('Manage destination bank accounts for manual transfers.') }}</p>
      </div>
      @if ($this->canVerify)
        <flux:button wire:click="createAccount" variant="primary" icon="plus">
          {{ __('Add Account') }}</flux:button>
      @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      @forelse ($this->accounts as $account)
        <div class="bg-surface-container-lowest rounded-xl p-5 border border-outline-variant/40 hover:border-navy/50 transition-colors group relative overflow-hidden {{ $account->is_active ? '' : 'opacity-60' }}">
          <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-lg bg-surface-container-high flex items-center justify-center font-heading text-xl font-bold text-navy">
              {{ $account->initial() }}
            </div>
            @if ($this->canVerify)
              <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button wire:click="editAccount({{ $account->id }})"
                  class="p-1.5 rounded-md text-on-surface-variant hover:bg-surface-container hover:text-navy transition-colors">
                  <span class="material-symbols-outlined text-[18px]">edit</span>
                </button>
                <button wire:click="confirmDeleteAccount({{ $account->id }})"
                  class="p-1.5 rounded-md text-on-surface-variant hover:bg-red-100 hover:text-red-600 transition-colors">
                  <span class="material-symbols-outlined text-[18px]">delete</span>
                </button>
              </div>
            @endif
          </div>
          <h3 class="font-body text-sm font-semibold text-on-surface uppercase mb-1">{{ $account->bank_name }}</h3>
          <p class="font-heading text-lg text-on-surface font-mono tracking-wider mb-2">{{ $account->account_number }}</p>
          <div class="font-body text-sm text-on-surface-variant">
            <p>A/N: {{ $account->account_holder }}</p>
            @if ($account->branch)
              <p class="text-xs mt-1">{{ __('Branch') }}: {{ $account->branch }}</p>
            @endif
          </div>
        </div>
      @empty
        <div class="col-span-full flex flex-col items-center justify-center text-on-surface-variant py-8">
          <span class="material-symbols-outlined text-[40px] mb-2 opacity-50">account_balance</span>
          <p class="font-body text-sm">{{ __('No payment accounts configured.') }}</p>
        </div>
      @endforelse
    </div>
  </div>

  {{-- Account Modal --}}
  <flux:modal wire:model="showAccountModal" class="min-w-[28rem]">
    <form wire:submit="saveAccount" class="flex flex-col gap-6">
      <div>
        <flux:heading size="lg">{{ $editingAccountId ? __('Edit Account') : __('Add Account') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Bank account used for manual transfer payments.') }}</flux:text>
      </div>

      <flux:input wire:model="bank_name" :label="__('Bank Name')" placeholder="Bank Central Asia (BCA)" />
      <flux:input wire:model="account_number" :label="__('Account Number')" placeholder="123 456 7890" />
      <flux:input wire:model="account_holder" :label="__('Account Holder')" />
      <flux:input wire:model="branch" :label="__('Branch (optional)')" placeholder="Kupang Utama" />
      <flux:switch wire:model="account_is_active" :label="__('Active')" />

      <div class="flex justify-end gap-3">
        <flux:button type="button" variant="ghost" wire:click="$set('showAccountModal', false)">
          {{ __('Cancel') }}</flux:button>
        <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
      </div>
    </form>
  </flux:modal>

  {{-- Fee Modal --}}
  <flux:modal wire:model="showFeeModal" class="min-w-[32rem]">
    <form wire:submit="saveFee" class="flex flex-col gap-5">
      <flux:heading size="lg">{{ $editingFeeId ? __('Edit Payment Fee') : __('Add Payment Fee') }}</flux:heading>
      <flux:select wire:model.live="fee_type" :label="__('Fee Type')">
        <flux:select.option value="registration">{{ __('Registration Fee') }}</flux:select.option>
        <flux:select.option value="assessment">{{ __('Entrance Assessment Test') }}</flux:select.option>
        <flux:select.option value="admin">{{ __('Admin Fee') }}</flux:select.option>
      </flux:select>
      <flux:input wire:model="fee_name" :label="__('Name')" />
      <flux:textarea wire:model="fee_description" :label="__('Description')" rows="2" />
      <flux:input wire:model="fee_amount" type="number" min="0" :label="__('Amount (IDR)')" />
      <div class="grid grid-cols-2 gap-4">
        <flux:select wire:model="fee_academic_year_id" :label="__('Academic Year')" :disabled="$fee_type === 'admin'">
          <flux:select.option value="">{{ __('Global') }}</flux:select.option>
          @foreach ($this->academicYears as $year)
            <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
          @endforeach
        </flux:select>
        <flux:select wire:model="fee_school_level_id" :label="__('School Level')" :disabled="$fee_type === 'admin'">
          <flux:select.option value="">{{ __('Global') }}</flux:select.option>
          @foreach ($this->schoolLevels as $level)
            <flux:select.option value="{{ $level->id }}">{{ $level->code }}</flux:select.option>
          @endforeach
        </flux:select>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <flux:input wire:model="fee_sort_order" type="number" min="0" :label="__('Sort Order')" />
        <flux:switch wire:model="fee_is_active" :label="__('Active')" />
      </div>
      <div class="flex justify-end gap-3">
        <flux:button type="button" variant="ghost" wire:click="$set('showFeeModal', false)">{{ __('Cancel') }}</flux:button>
        <flux:button type="submit" variant="primary">{{ __('Save Fee') }}</flux:button>
      </div>
    </form>
  </flux:modal>

  <flux:modal wire:model="showDeleteFeeModal" class="min-w-[24rem]">
    <div class="flex flex-col gap-6">
      <flux:heading size="lg">{{ __('Delete Payment Fee') }}</flux:heading>
      <flux:text>{{ __('Fees referenced by an invoice will be deactivated instead of deleted.') }}</flux:text>
      <div class="flex justify-end gap-3">
        <flux:button type="button" variant="ghost" wire:click="$set('showDeleteFeeModal', false)">{{ __('Cancel') }}</flux:button>
        <flux:button wire:click="deleteFee" variant="danger">{{ __('Delete') }}</flux:button>
      </div>
    </div>
  </flux:modal>

  {{-- Delete Account Modal --}}
  <flux:modal wire:model="showDeleteAccountModal" class="min-w-[24rem]">
    <div class="flex flex-col gap-6">
      <div>
        <flux:heading size="lg">{{ __('Delete Account') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Are you sure you want to delete this payment account?') }}</flux:text>
      </div>
      <div class="flex justify-end gap-3">
        <flux:button type="button" variant="ghost" wire:click="$set('showDeleteAccountModal', false)">
          {{ __('Cancel') }}</flux:button>
        <flux:button wire:click="deleteAccount" variant="danger">{{ __('Delete') }}</flux:button>
      </div>
    </div>
  </flux:modal>
</div>
