# Layout & Structure Guide

This reference provides detailed instructions on how to extract a raw HTML template into a clean Laravel Blade layout structure.

## 1. Creating the Master Layout (`resources/views/layouts/app.blade.php`)

The master layout is the foundation of your views. It should contain all the global HTML tags (`<!DOCTYPE html>`, `<html>`, `<head>`, `<body>`) that are repeated across raw HTML files.

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Global Styles / Fonts / CDNs -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#00288e',
                        // Add colors extracted from raw HTML
                    }
                }
            }
        }
    </script>
    
    @stack('styles')
</head>
<body class="bg-background text-on-surface font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Include Navbar -->
        @include('layouts.partials.navbar')

        <div class="flex flex-1">
            <!-- Include Sidebar if present in design -->
            @include('layouts.partials.sidebar')

            <!-- Main Content Area -->
            <main class="flex-1 w-full">
                @yield('content')
            </main>
        </div>

        <!-- Include Footer -->
        @include('layouts.partials.footer')
    </div>

    @stack('scripts')
</body>
</html>
```

## 2. Extracting Partials (`resources/views/layouts/partials/`)

Raw HTML designs often copy-paste the header, footer, and sidebar. Extract these into their own files.

### `navbar.blade.php`
```blade
<header class="h-16 bg-surface border-b border-outline-variant flex justify-between items-center px-6">
    <!-- Navbar Content from raw HTML -->
    <div class="flex items-center gap-4">
        <!-- Logo -->
    </div>
    <div class="flex items-center gap-4">
        <!-- User Profile / Auth links -->
        @auth
            <span>{{ Auth::user()->name }}</span>
        @else
            <a href="{{ route('login') }}">Login</a>
        @endauth
    </div>
</header>
```

### `sidebar.blade.php`
```blade
<aside class="w-64 bg-surface border-r border-outline-variant hidden md:block">
    <nav class="flex flex-col p-4 gap-2">
        <!-- Sidebar Links -->
        <a href="{{ route('dashboard') }}" class="px-4 py-2 hover:bg-surface-container rounded-lg">Dashboard</a>
    </nav>
</aside>
```

## 3. Cleaning Up Specific Pages

When applying this layout to a specific page (e.g. `dashboard.blade.php`), you **MUST REMOVE** the `<html>`, `<head>`, `<body>`, and any navbar/sidebar code from the original raw HTML.

**WRONG (Raw HTML):**
```html
<!DOCTYPE html>
<html>
<head>...</head>
<body>
    <nav>...</nav>
    <div class="content">...</div>
</body>
</html>
```

**CORRECT (Blade View):**
```blade
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Only the actual content area goes here -->
    <div class="content p-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <!-- ... -->
    </div>
@endsection
```
