<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new #[Title('User Management')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    // Create/Edit form state
    public bool $showUserModal = false;
    public ?int $editingUserId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public bool $is_active = true;

    // Delete confirmation state
    public bool $showDeleteModal = false;
    public ?int $deletingUserId = null;

    /**
     * Guard the page against non-administrators.
     */
    public function mount(): mixed
    {
        if (! Auth::user()->isSuperAdmin()) {
            return $this->redirectRoute('dashboard', navigate: true);
        }

        return null;
    }

    /**
     * Reset pagination when the search term changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when the role filter changes.
     */
    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Get all assignable roles.
     *
     * @return \Illuminate\Support\Collection<int, Role>
     */
    #[Computed]
    public function roles()
    {
        return Role::orderBy('name')->get();
    }

    /**
     * Get the paginated, filtered user list.
     */
    #[Computed]
    public function users()
    {
        return User::with('roles')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($sub): void {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter !== '', function ($query): void {
                $query->whereHas('roles', fn ($sub) => $sub->where('name', $this->roleFilter));
            })
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * Total number of users (for the header count).
     */
    #[Computed]
    public function totalUsers(): int
    {
        return User::count();
    }

    /**
     * Open the modal to create a new user.
     */
    public function createUser(): void
    {
        $this->resetForm();
        $this->showUserModal = true;
    }

    /**
     * Open the modal to edit an existing user.
     */
    public function editUser(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->roles->first()?->name ?? '';
        $this->is_active = $user->is_active;
        $this->resetErrorBag();
        $this->showUserModal = true;
    }

    /**
     * Persist the user (create or update).
     */
    public function saveUser(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ],
            'password' => [
                $this->editingUserId ? 'nullable' : 'required',
                'string',
                'min:8',
            ],
            'role' => ['required', Rule::exists('roles', 'name')],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->is_active = $validated['is_active'];

            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();
            $user->syncRoles([$validated['role']]);

            Flux::toast(variant: 'success', text: __('User updated successfully.'));
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['is_active'],
                'email_verified_at' => now(),
            ]);
            $user->syncRoles([$validated['role']]);

            Flux::toast(variant: 'success', text: __('User created successfully.'));
        }

        $this->showUserModal = false;
        $this->resetForm();
        unset($this->users);
    }

    /**
     * Toggle a user's active status.
     */
    public function toggleStatus(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === Auth::id()) {
            Flux::toast(variant: 'warning', text: __('You cannot deactivate your own account.'));

            return;
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        unset($this->users);
        Flux::toast(variant: 'success', text: __('User status updated.'));
    }

    /**
     * Ask for confirmation before deleting a user.
     */
    public function confirmDelete(int $userId): void
    {
        $this->deletingUserId = $userId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete the confirmed user.
     */
    public function deleteUser(): void
    {
        if (! $this->deletingUserId) {
            return;
        }

        $user = User::findOrFail($this->deletingUserId);

        if ($user->id === Auth::id()) {
            Flux::toast(variant: 'danger', text: __('You cannot delete your own account.'));
            $this->showDeleteModal = false;

            return;
        }

        $user->delete();

        $this->showDeleteModal = false;
        $this->deletingUserId = null;
        unset($this->users);
        Flux::toast(variant: 'success', text: __('User deleted successfully.'));
    }

    /**
     * Reset the create/edit form state.
     */
    private function resetForm(): void
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    /**
     * Build the two-letter initials for an avatar.
     */
    public function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last) ?: mb_strtoupper(mb_substr($name, 0, 2));
    }

    /**
     * Get the display metadata for a role badge.
     *
     * @return array{icon: string, classes: string}
     */
    public function roleBadge(?string $roleName): array
    {
        return match ($roleName) {
            'Administrator' => ['icon' => 'shield_person', 'classes' => 'bg-navy/10 text-navy'],
            'Admission Staff' => ['icon' => 'school', 'classes' => 'bg-gold/10 text-gold-dark'],
            'Finance' => ['icon' => 'account_balance', 'classes' => 'bg-royal/10 text-royal'],
            'Student' => ['icon' => 'person', 'classes' => 'bg-surface-container-high text-on-surface-variant'],
            default => ['icon' => 'help', 'classes' => 'bg-surface-container-high text-on-surface-variant'],
        };
    }
}; ?>

<div class="flex flex-col w-full max-w-7xl mx-auto gap-8 pb-16">

  {{-- Header --}}
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div class="flex flex-col gap-1">
      <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-navy text-[32px]">manage_accounts</span>
        <h1 class="font-heading text-3xl font-semibold text-navy">{{ __('User Management') }}</h1>
      </div>
      <p class="font-body text-base text-on-surface-variant max-w-2xl">
        {{ __('Manage user access and permissions for the Stella Gracia admission system.') }}</p>
    </div>
    <flux:button wire:click="createUser" variant="primary" icon="user-plus">
      {{ __('Add New User') }}
    </flux:button>
  </div>

  {{-- Filters --}}
  <div
    class="bg-white p-4 rounded-xl shadow-sm border border-outline-variant/30 flex flex-col sm:flex-row justify-between items-center gap-4">
    <div class="flex items-center gap-3 w-full sm:w-auto">
      <div class="relative w-full sm:w-72">
        <span
          class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
        <input wire:model.live.debounce.300ms="search" type="text"
          placeholder="{{ __('Search name or email...') }}"
          class="w-full bg-surface-container-lowest border border-outline-variant/50 font-body text-sm text-on-surface pl-10 pr-3 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy transition-all" />
      </div>
      <div class="relative">
        <select wire:model.live="roleFilter"
          class="appearance-none bg-surface-container-lowest border border-outline-variant/50 font-body text-sm text-on-surface-variant pl-3 pr-9 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy transition-all cursor-pointer">
          <option value="">{{ __('All Roles') }}</option>
          @foreach ($this->roles as $roleOption)
            <option value="{{ $roleOption->name }}">{{ $roleOption->name }}</option>
          @endforeach
        </select>
        <span
          class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[18px]">expand_more</span>
      </div>
    </div>
    <div class="font-body text-sm text-on-surface-variant font-medium">
      {{ __(':count total users', ['count' => $this->totalUsers]) }}
    </div>
  </div>

  {{-- Table --}}
  <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr
            class="bg-surface-container-lowest font-body text-xs text-on-surface-variant uppercase tracking-wider border-b border-outline-variant/30">
            <th class="py-4 px-5 font-semibold">{{ __('User') }}</th>
            <th class="py-4 px-5 font-semibold">{{ __('Role') }}</th>
            <th class="py-4 px-5 font-semibold">{{ __('Status') }}</th>
            <th class="py-4 px-5 font-semibold text-right">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="font-body text-sm text-on-surface divide-y divide-outline-variant/20">
          @forelse ($this->users as $user)
            @php($roleName = $user->roles->first()?->name)
            @php($badge = $this->roleBadge($roleName))
            <tr class="hover:bg-surface-container-lowest transition-colors group {{ $user->is_active ? '' : 'opacity-60' }}">
              <td class="py-3 px-5">
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-full bg-navy text-white flex items-center justify-center font-heading text-sm font-semibold shadow-sm shrink-0">
                    {{ $this->initials($user->name) }}
                  </div>
                  <div class="flex flex-col min-w-0">
                    <span class="font-medium truncate {{ $user->is_active ? '' : 'line-through' }}">
                      {{ $user->name }}</span>
                    <span class="text-on-surface-variant text-xs truncate">{{ $user->email }}</span>
                  </div>
                </div>
              </td>
              <td class="py-3 px-5">
                @if ($roleName)
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-body text-xs font-semibold {{ $badge['classes'] }}">
                    <span class="material-symbols-outlined text-[14px]">{{ $badge['icon'] }}</span>
                    {{ $roleName }}
                  </span>
                @else
                  <span class="font-body text-xs text-on-surface-variant italic">{{ __('No role') }}</span>
                @endif
              </td>
              <td class="py-3 px-5">
                @if ($user->is_active)
                  <span class="inline-flex items-center gap-1.5 font-body text-xs">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ __('Active') }}
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 font-body text-xs text-red-600">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ __('Inactive') }}
                  </span>
                @endif
              </td>
              <td class="py-3 px-5 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button wire:click="editUser({{ $user->id }})" title="{{ __('Edit User') }}"
                    class="w-8 h-8 rounded-full hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-navy transition-colors">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>
                  <button wire:click="toggleStatus({{ $user->id }})"
                    title="{{ $user->is_active ? __('Deactivate') : __('Activate') }}"
                    class="w-8 h-8 rounded-full hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-navy transition-colors">
                    <span class="material-symbols-outlined text-[18px]">
                      {{ $user->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                  </button>
                  <button wire:click="confirmDelete({{ $user->id }})" title="{{ __('Delete User') }}"
                    class="w-8 h-8 rounded-full hover:bg-red-100 flex items-center justify-center text-on-surface-variant hover:text-red-600 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="py-12 text-center">
                <div class="flex flex-col items-center justify-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-[40px] mb-2 opacity-50">person_off</span>
                  <p class="font-body text-sm">{{ __('No users found.') }}</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($this->users->hasPages())
      <div class="p-4 border-t border-outline-variant/30">
        {{ $this->users->links() }}
      </div>
    @endif
  </div>

  {{-- Create / Edit Modal --}}
  <flux:modal wire:model="showUserModal" class="min-w-[28rem]">
    <form wire:submit="saveUser" class="flex flex-col gap-6">
      <div>
        <flux:heading size="lg">
          {{ $editingUserId ? __('Edit User') : __('Add New User') }}</flux:heading>
        <flux:text class="mt-1">
          {{ $editingUserId ? __('Update the account details and role.') : __('Create a new system account and assign a role.') }}
        </flux:text>
      </div>

      <flux:input wire:model="name" :label="__('Full Name')" :placeholder="__('Enter full name')" />
      <flux:input wire:model="email" type="email" :label="__('Email Address')"
        placeholder="name@stellagracia.com" />
      <flux:input wire:model="password" type="password" :label="__('Password')"
        :placeholder="$editingUserId ? __('Leave blank to keep current password') : __('Minimum 8 characters')" viewable />

      <flux:select wire:model="role" :label="__('Role')" :placeholder="__('Select a role')">
        @foreach ($this->roles as $roleOption)
          <flux:select.option value="{{ $roleOption->name }}">{{ $roleOption->name }}</flux:select.option>
        @endforeach
      </flux:select>

      <flux:switch wire:model="is_active" :label="__('Active account')" />

      <div class="flex justify-end gap-3">
        <flux:button type="button" variant="ghost" wire:click="$set('showUserModal', false)">
          {{ __('Cancel') }}</flux:button>
        <flux:button type="submit" variant="primary">
          {{ $editingUserId ? __('Save Changes') : __('Create User') }}</flux:button>
      </div>
    </form>
  </flux:modal>

  {{-- Delete Confirmation Modal --}}
  <flux:modal wire:model="showDeleteModal" class="min-w-[24rem]">
    <div class="flex flex-col gap-6">
      <div>
        <flux:heading size="lg">{{ __('Delete User') }}</flux:heading>
        <flux:text class="mt-1">
          {{ __('Are you sure you want to delete this user? This action cannot be undone.') }}</flux:text>
      </div>
      <div class="flex justify-end gap-3">
        <flux:button type="button" variant="ghost" wire:click="$set('showDeleteModal', false)">
          {{ __('Cancel') }}</flux:button>
        <flux:button wire:click="deleteUser" variant="danger">{{ __('Delete') }}</flux:button>
      </div>
    </div>
  </flux:modal>
</div>
