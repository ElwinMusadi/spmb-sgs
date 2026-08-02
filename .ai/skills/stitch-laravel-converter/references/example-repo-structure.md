# Example Repository Structure

Before and after examples for a typical Stitch design export conversion.

## Before: Raw HTML Export

```
my-project/
  index.html                    # Dashboard (all-in-one)
  login.html                    # Auth page
  settings.html                 # Settings page
  assets/
    images/
      logo.png
      avatar.png
    icons/
      dashboard.svg
      settings.svg
```

### `index.html` (example)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            theme: { extend: { colors: { primary: "#00288e", ... } } }
        }
    </script>
    <style>
        .form-shadow { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <nav class="fixed top-0 h-16 bg-white border-b ..."><!-- navbar --></nav>
    <aside class="w-64 fixed left-0 top-16 ..."><!-- sidebar --></aside>
    <main class="ml-64 mt-16 p-8">
        <!-- page content -->
    </main>
    <footer class="..."><!-- footer --></footer>
</body>
</html>
```

### `login.html` (example)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- duplicate CDN, duplicate font links, duplicate tailwind-config -->
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <!-- form content -->
    </div>
</body>
</html>
```

## After: Laravel Blade Structure

```
my-project/
  resources/
    css/
      app.css                   # Tailwind v4 + @theme tokens
    js/
      app.js                    # Alpine.js registration (optional)
    images/
      logo.png                  # Copied from exports/assets/images/
      avatar.png
    views/
      layouts/
        app.blade.php           # App shell (navbar + sidebar + footer)
        auth.blade.php          # Auth shell (centered card, no sidebar)
        partials/
          navbar.blade.php
          sidebar.blade.php
          footer.blade.php
      components/
        button.blade.php
        input-group.blade.php
        icon.blade.php
        stat-card.blade.php
        empty-state.blade.php
        data-table.blade.php
      pages/
        dashboard.blade.php
        settings.blade.php
        create-post.blade.php
      auth/
        login.blade.php
        register.blade.php
        forgot-password.blade.php
  tailwind.config.js            # Tailwind v3 config (or omit for v4)
  vite.config.js
  package.json
```

### `resources/views/layouts/app.blade.php`
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL=100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-background text-on-surface font-body-md antialiased">
    @include('layouts.partials.navbar')
    @include('layouts.partials.sidebar')

    <main class="ml-64 mt-16 p-8 min-h-[calc(100vh-4rem)]">
        @yield('content')
    </main>

    @include('layouts.partials.footer')
    @stack('scripts')
</body>
</html>
```

### `resources/views/layouts/auth.blade.php`
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL=100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-background text-on-surface font-body-md antialiased flex items-center justify-center min-h-screen">
    <main class="w-full max-w-md p-8">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
```

### `resources/views/pages/dashboard.blade.php`
```blade
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="max-w-[1280px] mx-auto">
        <h1 class="font-headline-lg text-headline-lg mb-8">Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-stat-card>
                <x-icon name="people" class="text-primary text-[24px]" />
                <div>
                    <p class="text-label-md text-on-surface-variant">Total Users</p>
                    <p class="text-headline-lg font-headline text-on-surface">1,234</p>
                </div>
            </x-stat-card>

            <x-stat-card>
                <x-icon name="shopping_cart" class="text-secondary text-[24px]" />
                <div>
                    <p class="text-label-md text-on-surface-variant">Revenue</p>
                    <p class="text-headline-lg font-headline text-on-surface">$56,800</p>
                </div>
            </x-stat-card>

            <x-stat-card>
                <x-icon name="trending_up" class="text-error text-[24px]" />
                <div>
                    <p class="text-label-md text-on-surface-variant">Growth</p>
                    <p class="text-headline-lg font-headline text-on-surface">+12%</p>
                </div>
            </x-stat-card>
        </div>

        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-headline-md text-headline-md">Recent Transactions</h2>
                <x-button variant="outline">View All</x-button>
            </div>

            <x-data-table :headers="['ID', 'Customer', 'Amount', 'Status', 'Date']" />
        </x-card>
    </div>
@endsection
```

### `resources/views/auth/login.blade.php`
```blade
@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="bg-surface p-8 rounded-xl form-shadow">
        <div class="text-center mb-8">
            <h1 class="font-headline-lg text-headline-lg">Welcome Back</h1>
            <p class="text-body-md text-on-surface-variant mt-2">Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="flex flex-col gap-4">
                <x-input-group 
                    name="email" 
                    label="Email Address" 
                    type="email" 
                    required="true" 
                />

                <x-input-group 
                    name="password" 
                    label="Password" 
                    type="password" 
                    required="true" 
                />

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="remember" class="rounded border-outline-variant">
                        <span class="text-body-md text-on-surface">Remember me</span>
                    </label>

                    <a href="{{ route('password.request') }}" class="text-primary text-label-md">
                        Forgot password?
                    </a>
                </div>

                <x-button type="submit" variant="primary" class="w-full">Sign In</x-button>
            </div>
        </form>

        <p class="text-center text-body-md text-on-surface-variant mt-6">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-primary font-medium">Sign up</a>
        </p>
    </div>
@endsection
```

## Size Comparison

| Metric | Before (raw HTML) | After (Blade) |
|--------|-------------------|---------------|
| Total files | 2 HTML files | 9+ Blade files |
| Dashboard size | ~500 lines | ~60 lines |
| Login size | ~300 lines | ~55 lines |
| Repeated CDN | 2 copies | 0 copies |
| Shared navbar | 2 copies | 1 partial |
| Component reuse | 0 | 3+ components |
