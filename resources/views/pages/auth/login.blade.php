<x-layouts::auth-custom :title="__('Log in')">
  <main class="relative min-h-screen bg-surface p-4 sm:p-8 flex">
    <div class="m-auto w-full max-w-300">
      <div class="grid grid-cols-1 lg:grid-cols-12 bg-white rounded-2xl shadow-2xl overflow-hidden relative">

        {{-- Left Brand Panel (desktop only) --}}
        <div
          class="hidden lg:flex lg:col-span-6 relative flex-col justify-between p-8 xl:p-12 text-white bg-navy-dark bg-cover bg-center"
          style="background-image: url('{{ asset('images/gedung-stella-gracia.jpg') }}')">
          <div
            class="absolute inset-0 bg-linear-to-br from-navy-dark/90 via-navy/80 to-navy-dark/60 z-0 mix-blend-multiply">
          </div>

          {{-- Logo & School Name --}}
          <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3"
            aria-label="Stella Gracia School - Beranda">
            <div class="w-14 h-14 rounded-xl bg-white shadow-md flex items-center justify-center p-1 mr-2">
              <img src="{{ asset('images/logo-stella-gracia.png') }}" alt="Logo Stella Gracia School Kupang"
                class="h-full w-full object-contain">
            </div>
            <div>
              <p class="font-heading text-base font-bold leading-tight text-white">Stella Gracia School Kupang</p>
              <p class="font-body text-xs text-royal-dim leading-tight">Sistem Penerimaan Murid Baru</p>
            </div>
          </a>

          {{-- Tagline --}}
          <div class="relative z-10 max-w-md my-8">
            <h1 class="font-heading text-4xl font-bold text-white mb-4 leading-tight">Mulai perjalanan pendidikanmu.
            </h1>
            <p class="font-body text-base text-royal-dim">
              Akses dashboard pendaftar, lacak status pendaftaran, dan lengkapi langkah-langkah yang diperlukan untuk
              pendaftaran.
            </p>
          </div>

          {{-- Security Badge --}}
          <div class="relative z-10 flex items-center space-x-2 text-royal-dim text-xs font-medium">
            <span class="material-symbols-outlined text-[16px]">verified_user</span>
            <span>Secure Admissions Platform</span>
          </div>
        </div>

        {{-- Right Form Panel --}}
        <div class="col-span-1 lg:col-span-6 flex flex-col justify-center p-6 sm:p-10 xl:p-12 bg-white relative">

          {{-- Mobile Logo --}}
          <a href="{{ route('home') }}" class="lg:hidden flex items-center gap-3 mb-8"
            aria-label="Stella Gracia School - Beranda">
            <img src="{{ asset('images/logo-stella-gracia.png') }}" alt="Logo Stella Gracia School Kupang"
              class="h-10 w-10 rounded-lg object-contain">
            <div>
              <p class="font-heading text-base font-bold leading-tight text-navy-dark">Stella Gracia School Kupang</p>
              <p class="font-body text-xs text-on-surface-variant leading-tight">Sistem Penerimaan Murid Baru</p>
            </div>
          </a>

          {{-- Header --}}
          <div class="mb-8">
            <h2 class="font-heading text-2xl sm:text-3xl font-semibold text-on-surface mb-2">Selamat Datang</h2>
            <p class="font-body text-sm sm:text-base text-on-surface-variant">Silakan masuk untuk melanjutkan
              pendaftaran Anda.</p>
          </div>

          {{-- Session Status --}}
          <x-auth-session-status class="mb-6" :status="session('status')" />

          {{-- Passkey Login --}}
          <x-passkey-verify />

          {{-- Login Form --}}
          <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <x-auth-input name="email" :label="__('Email')" type="email" icon="person"
              placeholder="Masukkan email Anda" :value="old('email')" required autofocus autocomplete="email" />

            {{-- Password --}}
            <x-auth-input name="password" :label="__('Password')" icon="lock" placeholder="••••••••" required
              autocomplete="current-password" viewable />

            {{-- Remember Me & Forgot Password --}}
            <div class="flex items-center justify-between pt-1">
              <label class="flex items-center cursor-pointer group" for="remember">
                <div class="relative flex items-center justify-center w-5 h-5 mr-3">
                  <input
                    class="peer appearance-none w-5 h-5 rounded bg-surface checked:bg-navy-dark shadow-[0_0_0_1px_theme(colors.outline-variant)_inset] checked:shadow-none transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-navy-dark focus:ring-offset-2"
                    type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} />
                  <span
                    class="material-symbols-outlined absolute text-white text-[16px] opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity duration-200"
                    style="font-variation-settings: 'FILL' 1;">check</span>
                </div>
                <span
                  class="font-body text-sm text-on-surface-variant group-hover:text-on-surface transition-colors">{{ __('Ingat saya') }}</span>
              </label>

              @if (Route::has('password.request'))
                <a class="font-body text-sm font-semibold text-navy-dark hover:text-royal transition-colors"
                  href="{{ route('password.request') }}" wire:navigate>
                  {{ __('Lupa kata sandi?') }}
                </a>
              @endif
            </div>

            {{-- Submit --}}
            <button type="submit"
              class="w-full bg-navy text-white hover:bg-navy-dark transition-colors font-body text-sm font-semibold py-3 rounded-lg mt-6 shadow-md flex items-center justify-center gap-3 group relative overflow-hidden"
              data-test="login-button">
              <span class="relative z-10">{{ __('Login Portal') }}</span>
              <span
                class="material-symbols-outlined relative z-10 group-hover:translate-x-1 transition-transform">arrow_forward</span>
              <div
                class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out z-0">
              </div>
            </button>
          </form>

          {{-- Register Link --}}
          <div class="mt-8 pt-8 relative">
            <div
              class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-outline-variant to-transparent opacity-50">
            </div>
            <p class="text-center font-body text-sm text-on-surface-variant">
              {{ __('Belum punya akun?') }}
              <a class="font-semibold text-navy-dark hover:text-royal transition-colors ml-1"
                href="{{ route('register') }}" wire:navigate>
                {{ __('Daftar akun baru') }}
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </main>
</x-layouts::auth-custom>
