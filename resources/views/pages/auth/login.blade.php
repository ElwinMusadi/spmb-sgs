<x-layouts::auth :title="__('Log in')">
  <main class="relative min-h-screen bg-surface p-6 lg:p-12 flex flex-col items-center justify-center">
    <div
      class="grid grid-cols-1 lg:grid-cols-12 bg-white rounded-2xl shadow-xl overflow-hidden min-h-[700px] w-full max-w-[1200px] mx-auto relative">

      {{-- Left Brand Panel (desktop only) --}}
      <div class="hidden lg:flex lg:col-span-6 relative flex-col justify-between p-12 text-white bg-navy-dark"
        style="background-image: url('{{ asset('images/gedung-stella-gracia.jpg') }}')">
        <div class="absolute inset-0 bg-gradient-to-br from-navy-dark/90 via-navy/80 to-navy-dark/60 z-0 mix-blend-multiply">
        </div>

        {{-- Logo & School Name --}}
        <div class="relative z-10 flex items-start">
          <div class="w-16 h-16 rounded-xl bg-white shadow-md flex items-center justify-center p-1 mr-6">
            <img alt="Logo Stella Gracia School Kupang" class="w-full h-full object-contain"
              src="{{ asset('images/logo-stella-gracia.png') }}" />
          </div>
          <div>
            <h2 class="font-heading text-xl font-semibold text-white">Stella Gracia School</h2>
            <p class="font-body text-xs font-medium text-royal-dim uppercase tracking-wider mt-1">Admissions Portal</p>
          </div>
        </div>

        {{-- Tagline --}}
        <div class="relative z-10 max-w-md">
          <h1 class="font-heading text-5xl font-bold text-white mb-6 leading-tight">Mulai perjalanan pendidikanmu.</h1>
          <p class="font-body text-lg text-royal-dim">
            Akses dashboard pendaftar, lacak status pendaftaran, dan lengkapi langkah-langkah yang diperlukan untuk
            pendaftaran.
          </p>
        </div>

        {{-- Security Badge --}}
        <div class="relative z-10 flex items-center space-x-3 text-royal-dim text-xs font-medium">
          <span class="material-symbols-outlined text-[16px]">verified_user</span>
          <span>Secure Admissions Platform</span>
        </div>
      </div>

      {{-- Right Form Panel --}}
      <div class="col-span-1 lg:col-span-6 flex flex-col justify-center p-6 sm:p-12 xl:p-20 bg-white relative">

        {{-- Mobile Logo --}}
        <div class="lg:hidden flex items-center mb-12">
          <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center p-1 mr-3">
            <img alt="Logo Stella Gracia School Kupang" class="w-full h-full object-contain"
              src="{{ asset('images/logo-stella-gracia.png') }}" />
          </div>
          <div>
            <h2 class="font-heading text-xl font-semibold text-navy-dark">Stella Gracia School</h2>
            <p class="font-body text-xs font-medium text-on-surface-variant uppercase tracking-wider">Admissions Portal
            </p>
          </div>
        </div>

        {{-- Header --}}
        <div class="mb-12">
          <h2 class="font-heading text-3xl font-semibold text-on-surface mb-3">Selamat Datang</h2>
          <p class="font-body text-base text-on-surface-variant">Silakan masuk untuk melanjutkan pendaftaran Anda.</p>
        </div>

        {{-- Session Status --}}
        <x-auth-session-status class="mb-6" :status="session('status')" />

        {{-- Passkey Login --}}
        <x-passkey-verify />

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login.store') }}" class="space-y-6">
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
          <div class="pt-3">
            <button type="submit"
              class="w-full flex justify-center py-3 px-12 font-body text-sm font-semibold text-white bg-navy-dark rounded-lg hover:bg-navy focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-dark transition-all duration-200 shadow-md hover:shadow-lg active:scale-[0.98]"
              data-test="login-button">
              {{ __('Login Portal') }}
            </button>
          </div>
        </form>

        {{-- Register Link --}}
        <div class="mt-12 pt-12 relative">
          <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-outline-variant to-transparent opacity-50">
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
  </main>
</x-layouts::auth>
