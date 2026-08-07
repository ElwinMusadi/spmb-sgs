<?php

use Database\Seeders\PermissionSeeder;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

new #[Title('Roles & Permissions')] class extends Component {
    public ?int $selectedRoleId = null;

    /**
     * Map of permission id => bool for the selected role.
     *
     * @var array<int, bool>
     */
    public array $permissionState = [];

    /**
     * Guard the page and preselect the first role.
     */
    public function mount(): mixed
    {
        if (! Auth::user()->isSuperAdmin()) {
            return $this->redirectRoute('dashboard', navigate: true);
        }

        $this->selectedRoleId = Role::orderBy('name')->value('id');
        $this->loadPermissionState();

        return null;
    }

    /**
     * Reload permission state when the selected role changes.
     */
    public function updatedSelectedRoleId(): void
    {
        $this->loadPermissionState();
    }

    /**
     * Get all roles.
     *
     * @return \Illuminate\Support\Collection<int, Role>
     */
    #[Computed]
    public function roles()
    {
        return Role::withCount('permissions')->orderBy('name')->get();
    }

    /**
     * The currently selected role.
     */
    #[Computed]
    public function selectedRole(): ?Role
    {
        if (! $this->selectedRoleId) {
            return null;
        }

        return Role::find($this->selectedRoleId);
    }

    /**
     * The grouped permission catalog for rendering the matrix.
     *
     * @return array<string, array<int, array{name: string, label: string}>>
     */
    #[Computed]
    public function modules(): array
    {
        return PermissionSeeder::MODULES;
    }

    /**
     * Lookup of permission name => id.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function permissionIds(): array
    {
        return Permission::pluck('id', 'name')->all();
    }

    /**
     * Load the checked state for the selected role.
     */
    private function loadPermissionState(): void
    {
        $this->permissionState = [];

        $role = $this->selectedRole;
        $assignedIds = $role ? $role->permissions->pluck('id')->all() : [];

        foreach (Permission::pluck('id') as $id) {
            $this->permissionState[$id] = in_array($id, $assignedIds, true);
        }
    }

    /**
     * Persist the permission matrix for the selected role.
     */
    public function save(): void
    {
        $role = $this->selectedRole;

        if (! $role) {
            return;
        }

        if ($role->name === 'Administrator') {
            Flux::toast(variant: 'warning', text: __('The Administrator role always retains full access.'));
            $this->loadPermissionState();

            return;
        }

        $grantedIds = array_keys(array_filter($this->permissionState));
        $granted = Permission::whereIn('id', $grantedIds)->pluck('name')->all();

        $role->syncPermissions($granted);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        unset($this->roles);
        Flux::toast(variant: 'success', text: __('Permissions updated for :role.', ['role' => $role->name]));
    }

    /**
     * Metadata for a module icon.
     */
    public function moduleIcon(string $module): string
    {
        return match ($module) {
            'Dashboard' => 'dashboard',
            'Applicants' => 'group',
            'Documents' => 'folder_shared',
            'Finance' => 'payments',
            'User Management' => 'manage_accounts',
            default => 'tune',
        };
    }
}; ?>

<div class="flex flex-col w-full max-w-7xl mx-auto gap-8 pb-16">

  {{-- Header --}}
  <div class="flex flex-col gap-1">
    <div class="flex items-center gap-3">
      <span class="material-symbols-outlined text-navy text-[32px]">admin_panel_settings</span>
      <h1 class="font-heading text-3xl font-semibold text-navy">{{ __('Roles & Permissions') }}</h1>
    </div>
    <p class="font-body text-base text-on-surface-variant max-w-2xl">
      {{ __('Configure module access for each role in the admission system.') }}</p>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

    {{-- Role List --}}
    <div class="lg:col-span-4 flex flex-col gap-3">
      <h2 class="font-heading text-sm font-semibold text-on-surface-variant uppercase tracking-wider">
        {{ __('Roles') }}</h2>
      @foreach ($this->roles as $roleItem)
        <button wire:click="$set('selectedRoleId', {{ $roleItem->id }})"
          wire:loading.attr="disabled"
          class="flex items-center justify-between p-4 rounded-xl border text-left transition-all
          {{ $selectedRoleId === $roleItem->id ? 'border-navy bg-navy/5 ring-1 ring-navy' : 'border-outline-variant/40 bg-white hover:border-navy/50' }}">
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0
              {{ $selectedRoleId === $roleItem->id ? 'bg-navy text-white' : 'bg-surface-container text-on-surface-variant' }}">
              <span class="material-symbols-outlined text-[20px]">badge</span>
            </div>
            <div>
              <p class="font-body text-sm font-semibold text-on-surface">{{ $roleItem->name }}</p>
              <p class="font-body text-xs text-on-surface-variant">
                {{ __(':count permissions', ['count' => $roleItem->permissions_count]) }}</p>
            </div>
          </div>
          @if ($selectedRoleId === $roleItem->id)
            <span class="material-symbols-outlined text-navy text-[20px]">chevron_right</span>
          @endif
        </button>
      @endforeach
    </div>

    {{-- Permission Matrix --}}
    <div class="lg:col-span-8">
      <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="h-2 w-full bg-gradient-to-r from-navy to-royal"></div>
        <div class="p-6 flex flex-col gap-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="font-heading text-lg font-semibold text-navy">
                {{ $this->selectedRole?->name ?? __('Select a role') }}</h2>
              <p class="font-body text-sm text-on-surface-variant">{{ __('Module access rights') }}</p>
            </div>
          </div>

          @if ($this->selectedRole?->name === 'Administrator')
            <div class="flex items-center gap-3 p-4 rounded-lg bg-navy/5 border border-navy/20">
              <span class="material-symbols-outlined text-navy">verified_user</span>
              <p class="font-body text-sm text-on-surface-variant">
                {{ __('The Administrator role always has full access to every module.') }}</p>
            </div>
          @endif

          <div class="flex flex-col gap-6">
            @foreach ($this->modules as $module => $permissions)
              <div class="flex flex-col gap-2">
                <h3 class="font-body text-xs font-semibold text-on-surface uppercase tracking-wider flex items-center gap-2">
                  <span class="material-symbols-outlined text-[18px] text-navy">{{ $this->moduleIcon($module) }}</span>
                  {{ $module }}
                </h3>
                <div class="flex flex-col divide-y divide-outline-variant/20">
                  @foreach ($permissions as $permission)
                    <div class="flex items-center justify-between py-3">
                      <div class="flex flex-col">
                        <span class="font-body text-sm text-on-surface">{{ __($permission['label']) }}</span>
                        <span class="font-body text-xs text-on-surface-variant font-mono">{{ $permission['name'] }}</span>
                      </div>
                      <flux:switch wire:model="permissionState.{{ $this->permissionIds[$permission['name']] ?? 0 }}"
                        :disabled="$this->selectedRole?->name === 'Administrator'" />
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>

          <div class="flex justify-end pt-2 border-t border-outline-variant/30">
            <flux:button wire:click="save" variant="primary" icon="check"
              :disabled="$this->selectedRole?->name === 'Administrator'">
              {{ __('Save Configuration') }}
            </flux:button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
