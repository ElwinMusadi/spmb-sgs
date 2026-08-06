@props([
    'sidebar' => false,
    'href' => route('home'),
])

@if ($sidebar)
  <a href="{{ $href }}"
    class="flex items-center gap-3 px-2 h-10 min-w-0 in-data-flux-sidebar-collapsed-desktop:w-10 in-data-flux-sidebar-collapsed-desktop:px-0 in-data-flux-sidebar-collapsed-desktop:justify-center in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:absolute in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:opacity-0"
    data-flux-sidebar-brand aria-label="Stella Gracia School - Beranda" {{ $attributes }}>
    <img src="{{ asset('images/logo-stella-gracia.png') }}" alt="Logo Stella Gracia School Kupang"
      class="h-10 w-10 shrink-0 rounded-lg object-contain">

    <div class="text-left min-w-0 truncate in-data-flux-sidebar-collapsed-desktop:hidden">
      <p class="font-heading text-[15px] font-bold leading-tight text-navy-dark dark:text-white truncate">Stella Gracia
        School</p>
      <p class="font-body text-[10px] text-on-surface-variant dark:text-gray-400 leading-tight mt-0.5 truncate">Sistem
        Penerimaan Murid Baru</p>
    </div>
  </a>
@else
  <a href="{{ $href }}" class="flex items-center gap-3 h-10 min-w-0" data-flux-brand
    aria-label="Stella Gracia School - Beranda" {{ $attributes }}>
    <img src="{{ asset('images/logo-stella-gracia.png') }}" alt="Logo Stella Gracia School Kupang"
      class="h-10 w-10 shrink-0 rounded-lg object-contain">

    <div class="hidden sm:block text-left min-w-0">
      <p class="font-heading text-base font-bold leading-tight text-navy-dark dark:text-white truncate">Stella Gracia
        School</p>
      <p class="font-body text-xs text-on-surface-variant dark:text-gray-400 leading-tight mt-0.5 truncate">Sistem
        Penerimaan Murid Baru</p>
    </div>
  </a>
@endif
