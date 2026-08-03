@props([
    'name',
    'label',
    'type' => 'text',
    'icon' => null,
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'autofocus' => false,
    'autocomplete' => null,
    'viewable' => false,
])

<div class="space-y-1 group" x-data="{ showPassword: false }">
  <label
    class="block text-sm font-medium text-on-surface transition-colors group-focus-within:text-navy-dark"
    for="{{ $name }}"
  >
    {{ $label }}
  </label>

  <div class="relative">
    {{-- Leading Icon --}}
    @if ($icon)
      <div
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant group-focus-within:text-navy-dark transition-colors"
      >
        <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
      </div>
    @endif

    {{-- Input Field --}}
    @if ($viewable)
      <input
        id="{{ $name }}"
        name="{{ $name }}"
        :type="showPassword ? 'text' : 'password'"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        autocomplete="{{ $autocomplete }}"
        @if ($required) required @endif
        @if ($autofocus) autofocus @endif
        class="block w-full {{ $icon ? 'pl-10' : 'pl-3' }} pr-10 py-2.5 font-body text-base text-on-surface bg-surface rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-dark focus:bg-white transition-all duration-200 ease-in-out shadow-[0_0_0_1px_theme(colors.outline-variant)_inset]
          @error($name) ring-2 ring-red-500 @enderror"
      />

      {{-- Password Visibility Toggle --}}
      <button
        type="button"
        x-on:click="showPassword = !showPassword"
        aria-label="Toggle password visibility"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-on-surface-variant hover:text-navy-dark transition-colors focus:outline-none"
      >
        <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility' : 'visibility_off'">visibility_off</span>
      </button>
    @else
      <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        autocomplete="{{ $autocomplete }}"
        @if ($required) required @endif
        @if ($autofocus) autofocus @endif
        class="block w-full {{ $icon ? 'pl-10' : 'pl-3' }} pr-3 py-2.5 font-body text-base text-on-surface bg-surface rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-dark focus:bg-white transition-all duration-200 ease-in-out shadow-[0_0_0_1px_theme(colors.outline-variant)_inset]
          @error($name) ring-2 ring-red-500 @enderror"
      />
    @endif
  </div>

  {{-- Slot for additional content (e.g., password hint) --}}
  {{ $slot }}

  {{-- Validation Error --}}
  @error($name)
    <p class="text-sm text-red-600">{{ $message }}</p>
  @enderror
</div>
