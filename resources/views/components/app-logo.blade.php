@props([
    'sidebar' => false,
])

@if ($sidebar)
  <flux:sidebar.brand href="{{ route('home') }}" class="gap-3" aria-label="Stella Gracia School - Beranda" {{ $attributes }}>
    <x-slot name="logo">
      <img src="{{ asset('images/logo-stella-gracia.png') }}" alt="Logo Stella Gracia School Kupang"
        class="h-10 w-10 rounded-lg object-contain">
    </x-slot>
    <div class="text-left">
      <p class="font-heading text-sm font-bold leading-tight text-navy-dark dark:text-white">Stella Gracia School Kupang</p>
      <p class="font-body text-[10px] text-on-surface-variant dark:text-gray-400 leading-tight mt-0.5">Sistem Penerimaan Murid Baru</p>
    </div>
  </flux:sidebar.brand>
@else
  <flux:brand href="{{ route('home') }}" class="gap-3" aria-label="Stella Gracia School - Beranda" {{ $attributes }}>
    <x-slot name="logo">
      <img src="{{ asset('images/logo-stella-gracia.png') }}" alt="Logo Stella Gracia School Kupang"
        class="h-10 w-10 rounded-lg object-contain">
    </x-slot>
    <div class="hidden sm:block text-left">
      <p class="font-heading text-base font-bold leading-tight text-navy-dark dark:text-white">Stella Gracia School Kupang</p>
      <p class="font-body text-xs text-on-surface-variant dark:text-gray-400 leading-tight mt-0.5">Sistem Penerimaan Murid Baru</p>
    </div>
  </flux:brand>
@endif
