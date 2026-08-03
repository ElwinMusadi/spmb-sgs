<nav x-data="{ mobileMenuOpen: false }"
  class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-white/90 backdrop-blur-md border-b border-outline-variant/30">
  <div class="max-w-[1280px] mx-auto px-6 lg:px-10">
    <div class="flex items-center justify-between h-[72px]">
      {{-- Logo --}}
      <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Stella Gracia School - Beranda">
        <img src="{{ asset('images/logo-stella-gracia.png') }}" alt="Logo Stella Gracia School Kupang"
          class="h-10 w-10 rounded-lg object-contain">
        <div class="hidden sm:block">
          <p class="font-heading text-base font-bold leading-tight text-navy-dark">Stella Gracia School Kupang
          </p>
          <p class="font-body text-xs text-on-surface-variant leading-tight">Sistem Penerimaan Murid Baru</p>
        </div>
      </a>

      {{-- Desktop Navigation --}}
      <div class="hidden lg:flex items-center gap-8">
        <a href="#beranda"
          class="font-body text-sm font-medium text-on-surface hover:text-royal transition-colors">Beranda</a>
        <a href="#keunggulan"
          class="font-body text-sm font-medium text-on-surface-variant hover:text-royal transition-colors">Keunggulan</a>
        <a href="#alur-pendaftaran"
          class="font-body text-sm font-medium text-on-surface-variant hover:text-royal transition-colors">Alur
          Pendaftaran</a>
        <a href="#kontak"
          class="font-body text-sm font-medium text-on-surface-variant hover:text-royal transition-colors">Kontak</a>
      </div>

      {{-- Desktop CTA --}}
      <div class="hidden lg:flex items-center gap-3">
        @auth
          <a href="{{ route('dashboard') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-navy text-white font-body text-sm font-semibold hover:bg-navy-dark transition-colors">
            Dashboard
          </a>
        @else
          <a href="{{ route('login') }}"
            class="inline-flex items-center px-5 py-2.5 rounded-lg border border-outline-variant text-on-surface font-body text-sm font-medium hover:bg-surface-container transition-colors">
            Masuk
          </a>
          @if (Route::has('register'))
            <a href="{{ route('register') }}"
              class="inline-flex items-center px-5 py-2.5 rounded-lg bg-navy text-white font-body text-sm font-semibold hover:bg-navy-dark transition-colors shadow-md">
              Daftar Sekarang
            </a>
          @endif
        @endauth
      </div>

      {{-- Mobile Menu Toggle --}}
      <button @click="mobileMenuOpen = !mobileMenuOpen"
        class="lg:hidden p-2 rounded-lg hover:bg-surface-container transition-colors" :aria-expanded="mobileMenuOpen"
        aria-label="Toggle navigation menu">
        <svg x-show="!mobileMenuOpen" class="w-6 h-6 text-on-surface" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6 text-on-surface" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden pb-4 border-t border-outline-variant/30">
      <div class="flex flex-col gap-1 pt-3">
        <a href="#beranda" @click="mobileMenuOpen = false"
          class="px-4 py-2.5 rounded-lg font-body text-sm font-medium text-on-surface hover:bg-surface-container transition-colors">Beranda</a>
        <a href="#keunggulan" @click="mobileMenuOpen = false"
          class="px-4 py-2.5 rounded-lg font-body text-sm font-medium text-on-surface-variant hover:bg-surface-container transition-colors">Keunggulan</a>
        <a href="#alur-pendaftaran" @click="mobileMenuOpen = false"
          class="px-4 py-2.5 rounded-lg font-body text-sm font-medium text-on-surface-variant hover:bg-surface-container transition-colors">Alur
          Pendaftaran</a>
        <a href="#kontak" @click="mobileMenuOpen = false"
          class="px-4 py-2.5 rounded-lg font-body text-sm font-medium text-on-surface-variant hover:bg-surface-container transition-colors">Kontak</a>
      </div>
      <div class="flex flex-col gap-2 pt-3 mt-2 border-t border-outline-variant/30 px-4">
        @auth
          <a href="{{ route('dashboard') }}"
            class="w-full text-center px-5 py-2.5 rounded-lg bg-navy text-white font-body text-sm font-semibold hover:bg-navy-dark transition-colors">
            Dashboard
          </a>
        @else
          <a href="{{ route('login') }}"
            class="w-full text-center px-5 py-2.5 rounded-lg border border-outline-variant text-on-surface font-body text-sm font-medium hover:bg-surface-container transition-colors">
            Masuk
          </a>
          @if (Route::has('register'))
            <a href="{{ route('register') }}"
              class="w-full text-center px-5 py-2.5 rounded-lg bg-navy text-white font-body text-sm font-semibold hover:bg-navy-dark transition-colors">
              Daftar Sekarang
            </a>
          @endif
        @endauth
      </div>
    </div>
  </div>
</nav>

{{-- Spacer for fixed navbar --}}
<div class="h-[72px]"></div>
