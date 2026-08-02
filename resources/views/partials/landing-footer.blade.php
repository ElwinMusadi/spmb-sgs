<footer id="kontak" class="bg-navy-dark text-white">
  <div class="max-w-[1280px] mx-auto px-6 lg:px-10">
    {{-- Main Footer Content --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 py-16">
      {{-- School Info --}}
      <div class="lg:col-span-2">
        <div class="flex items-center gap-3 mb-4">
          <img src="{{ asset('images/logo-stella-gracia.png') }}" alt="Logo Stella Gracia School"
            class="h-12 w-12 rounded-lg object-contain bg-white/10 p-1">
          <div>
            <p class="font-heading text-lg font-bold leading-tight">Stella Gracia School</p>
            <p class="font-body text-sm text-royal-dim leading-tight">Kupang, Nusa Tenggara Timur</p>
          </div>
        </div>
        <p class="font-body text-sm text-royal-dim/80 leading-relaxed max-w-md">
          Membentuk generasi berkarakter, berprestasi, dan siap menghadapi masa depan dengan fondasi iman dan ilmu yang
          kokoh.
        </p>
      </div>

      {{-- Quick Links --}}
      <div>
        <h4 class="font-heading text-sm font-semibold uppercase tracking-wider text-gold mb-4">Navigasi</h4>
        <ul class="flex flex-col gap-2.5">
          <li><a href="#beranda" class="font-body text-sm text-royal-dim hover:text-white transition-colors">Beranda</a>
          </li>
          <li><a href="#keunggulan"
              class="font-body text-sm text-royal-dim hover:text-white transition-colors">Keunggulan</a></li>
          <li><a href="#alur-pendaftaran"
              class="font-body text-sm text-royal-dim hover:text-white transition-colors">Alur Pendaftaran</a></li>
          @if (Route::has('login'))
            <li><a href="{{ route('login') }}"
                class="font-body text-sm text-royal-dim hover:text-white transition-colors">Portal Masuk</a></li>
          @endif
        </ul>
      </div>

      {{-- Contact --}}
      <div>
        <h4 class="font-heading text-sm font-semibold uppercase tracking-wider text-gold mb-4">Hubungi Kami</h4>
        <ul class="flex flex-col gap-2.5">
          <li class="flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 text-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="font-body text-sm text-royal-dim">Jl. Timor Raya, Kupang, NTT 85228</span>
          </li>
          <li class="flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 text-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span class="font-body text-sm text-royal-dim">info@stellagracia.sch.id</span>
          </li>
          <li class="flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 text-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <span class="font-body text-sm text-royal-dim">(0380) 123-4567</span>
          </li>
        </ul>
      </div>
    </div>

    {{-- Copyright Bar --}}
    <div class="border-t border-white/10 py-6">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="font-body text-xs text-royal-dim/60">
          &copy; {{ date('Y') }} Stella Gracia School Kupang. Hak cipta dilindungi.
        </p>
        <p class="font-body text-xs text-royal-dim/60">
          Sistem Penerimaan Murid Baru (SPMB)
        </p>
      </div>
    </div>
  </div>
</footer>
