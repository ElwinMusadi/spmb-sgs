<x-layouts::auth-custom :title="__('Register')">
  <main class="relative min-h-screen bg-surface p-4 sm:p-8 flex">
    {{-- Ambient Background (moved to body level for unified card) --}}
    <div class="fixed inset-0 z-0 pointer-events-none opacity-30">
      <svg height="100%" preserveAspectRatio="none" viewBox="0 0 100 100" width="100%" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <radialGradient cx="20%" cy="20%" id="grad1" r="60%">
            <stop offset="0%" stop-color="#bac3ff" stop-opacity="0.3"></stop>
            <stop offset="100%" stop-color="#f8f9fa" stop-opacity="0"></stop>
          </radialGradient>
          <radialGradient cx="80%" cy="80%" id="grad2" r="50%">
            <stop offset="0%" stop-color="#bd9c4f" stop-opacity="0.15"></stop>
            <stop offset="100%" stop-color="#f8f9fa" stop-opacity="0"></stop>
          </radialGradient>
        </defs>
        <rect fill="url(#grad1)" height="100" width="100"></rect>
        <rect fill="url(#grad2)" height="100" width="100"></rect>
      </svg>
    </div>

    <div class="m-auto w-full max-w-300">
      <div class="grid grid-cols-1 lg:grid-cols-12 bg-white rounded-2xl shadow-xl overflow-hidden relative z-10">

        {{-- Brand Side (Left on Desktop, Top on Mobile) --}}
        <div
          class="lg:col-span-5 flex flex-col justify-center h-full order-2 lg:order-1 relative bg-surface-container p-8 lg:p-10 border-t lg:border-t-0 lg:border-r border-outline-variant/30">
          <div class="absolute -left-12 -top-12 w-64 h-64 bg-royal-dim/20 rounded-full blur-3xl mix-blend-multiply">
          </div>

          {{-- Decorative Gold Accent --}}
          <div class="absolute top-0 left-0 w-1.5 h-full bg-gold"></div>

          <div class="relative z-10">
            {{-- Logo & School Name --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 mb-10"
              aria-label="Stella Gracia School - Beranda">
              <img src="{{ asset('images/logo-stella-gracia.png') }}" alt="Logo Stella Gracia School Kupang"
                class="h-12 w-12 rounded-lg object-contain">
              <div>
                <p class="font-heading text-base font-bold leading-tight text-navy-dark">Stella Gracia School Kupang</p>
                <p class="font-body text-xs text-on-surface-variant leading-tight">Sistem Penerimaan Murid Baru</p>
              </div>
            </a>

            {{-- Headline --}}
            <h1 class="font-heading text-4xl lg:text-5xl font-bold text-navy-dark mb-6">
              Mulai<br />Perjalananmu.
            </h1>

            <p class="font-body text-base lg:text-lg text-on-surface-variant mb-10 max-w-md">
              Bergabunglah dengan Stella Gracia School. Buat akun untuk memulai proses pendaftaran, lacak status
              penerimaan, dan kelola dokumen yang diperlukan.
            </p>

            {{-- Feature Highlights --}}
            <div class="space-y-6">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-navy flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-royal-dim text-[20px]">how_to_reg</span>
                </div>
                <div>
                  <h3 class="font-heading text-xl font-semibold text-on-surface mb-1">Proses Sederhana</h3>
                  <p class="font-body text-sm text-on-surface-variant">Selesaikan langkah pendaftaran sesuai kecepatan
                    Anda.</p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gold-dark flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-gold-pale text-[20px]">security</span>
                </div>
                <div>
                  <h3 class="font-heading text-xl font-semibold text-on-surface mb-1">Portal Aman</h3>
                  <p class="font-body text-sm text-on-surface-variant">Data pribadi dan dokumen Anda dienkripsi dengan
                    aman.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Form Side (Right on Desktop, Bottom on Mobile) --}}
        <div
          class="lg:col-span-7 flex flex-col justify-center h-full order-1 lg:order-2 p-6 sm:p-10 xl:px-12 xl:py-8 bg-white relative">
          <div class="w-full max-w-lg mx-auto">
            {{-- Header --}}
            <div class="mb-10 text-center lg:text-left">
              <h2 class="font-heading text-2xl sm:text-3xl font-semibold text-navy-dark mb-1">Buat Akun</h2>
              <p class="font-body text-sm sm:text-base text-on-surface-variant">Pendaftaran Tahun Ajaran
                {{ date('Y') }}/{{ date('Y') + 1 }}</p>
            </div>

            {{-- Session Status --}}
            <x-auth-session-status class="mb-6" :status="session('status')" />

            {{-- Registration Form --}}
            <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
              @csrf

              {{-- Full Name --}}
              <x-auth-input name="name" :label="__('Nama Lengkap Pendaftar')" type="text" icon="person" placeholder="cth. John Doe"
                :value="old('name')" required autofocus autocomplete="name" />

              {{-- Email --}}
              <x-auth-input name="email" :label="__('Alamat Email')" type="email" icon="mail"
                placeholder="john@example.com" :value="old('email')" required autocomplete="email" />

              {{-- Password --}}
              <x-auth-input name="password" :label="__('Buat Password')" icon="lock" placeholder="••••••••" required
                autocomplete="new-password" viewable>
                <p class="font-body text-xs text-on-surface-variant/70 mt-1">Minimal 8 karakter.</p>
              </x-auth-input>

              {{-- Confirm Password --}}
              <x-auth-input name="password_confirmation" :label="__('Konfirmasi Password')" icon="lock" placeholder="••••••••"
                required autocomplete="new-password" viewable />

              {{-- Verification Notice --}}
              {{-- <div class="bg-royal-pale/30 rounded-lg p-3 flex gap-3 items-start mt-6">
                <span
                  class="material-symbols-outlined text-navy-dark text-[20px] shrink-0 mt-0.5">mark_email_read</span>
                <p class="font-body text-sm text-on-surface-variant">
                  <strong>Verifikasi Diperlukan:</strong> Setelah mendaftar, tautan verifikasi akan dikirim ke Email
                  Anda.
                </p>
              </div> --}}

              {{-- Submit Button --}}
              <button type="submit"
                class="w-full bg-navy text-white hover:bg-navy-dark transition-colors font-body text-sm font-semibold py-3 rounded-lg mt-6 shadow-md flex items-center justify-center gap-3 group relative overflow-hidden"
                data-test="register-user-button">
                <span class="relative z-10">{{ __('Buat Akun') }}</span>
                <span
                  class="material-symbols-outlined relative z-10 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                <div
                  class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out z-0">
                </div>
              </button>

              {{-- Login Link --}}
              <div class="text-center mt-6 pt-6 border-t border-outline-variant/30">
                <p class="font-body text-sm sm:text-base text-on-surface-variant">
                  {{ __('Sudah punya akun?') }}
                  <a class="text-royal font-semibold hover:text-navy-dark transition-colors underline decoration-2 underline-offset-4 ml-1"
                    href="{{ route('login') }}" wire:navigate>
                    {{ __('Masuk di sini') }}
                  </a>
                </p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</x-layouts::auth-custom>
