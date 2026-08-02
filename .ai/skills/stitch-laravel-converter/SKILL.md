---
name: stitch-laravel-converter
description: Analyzes and converts raw HTML/Tailwind from design exports (Stitch/Figma) into clean Laravel Blade components, layouts, and views following DRY (Don't Repeat Yourself) principles. Use this when the user asks to functionalize or clean up raw HTML designs.
license: MIT
metadata:
  version: "2.0.0"
  domain: frontend
  triggers: stitch, convert design, clean html, create layout, blade components, dry, tailwind html to blade
  role: frontend engineer
  scope: implementation
  output-format: code
---

# Stitch to Laravel Blade Converter

Specialist in transforming raw HTML designs (usually with long and repetitive Tailwind CDNs) into a clean, modular, and efficient Laravel view architecture.

## Core Workflow

1. **Extract Design Tokens** — Parse the Tailwind CDN config (`<script id="tailwind-config">`), font imports, and custom CSS blocks. Map colors, fonts, and utilities into `resources/css/app.css` (`@theme` for Tailwind v4) or `tailwind.config.js` (v3). Build a quick token reference sheet.
2. **Map Pages to Views** — Inventory all raw HTML pages. Identify shared chrome (navbar, sidebar, footer) and layout variants (app, auth, landing). Create `resources/views/layouts/` and `resources/views/layouts/partials/`.
3. **Apply Semantic Grouping** — Classify each page body block into regions: navbar, sidebar, footer, table/card list, form group, empty state, stat card, search bar, modal. Use heuristics to decide what becomes a partial vs. a component vs. inline content.
4. **Create Master Layout** — Create the main layout file(s). Move all global elements into this file and use `@yield('content')` or `{{ $slot }}` for page-specific content. Remove all CDN scripts.
5. **Extract Reusable Components** — Identify frequently used small UI elements and separate them into `resources/views/components/` using the component naming contract. Use `@props`, `$attributes->merge()`, and `@slot` patterns.
6. **Clean Up Specific Pages (Views)** — Remove the global structure (`<html>`, `<head>`, `<body>`, Navbar, etc.) from specific pages. Add `@extends`, `@section`, and `@push` directives.
7. **Migrate Assets via Vite** — Move images and icons from the raw export into `resources/` and reference them with `Vite::asset()` or `asset()`. Set up `vite.config.js` and `@vite` directives. Run `npm run build` to verify.
8. **Implement Optional Alpine.js** — Add interactivity only where needed (dropdowns, modals, tabs). Keep `x-data` scoped to the smallest possible region. Use `aria-*` attributes for accessibility.
9. **Run Accessibility Pass** — Verify `alt` text, `aria-label` on icon buttons, heading order, label-input association via `for`/`id`, modal semantics, and focus management.
10. **Validate Output** — Run the validation checklist: no duplicate html/head/body, no leftover CDN, no mega-files, all parts extracted, assets load, styles match original.

## Reference Guide

Load detailed guidance based on context:

| Topic | Reference | Load When |
|-------|-----------|-----------|
| Design Tokens | `references/design-tokens.md` | Parsing Tailwind CDN config, extracting colors/fonts/spacing into CSS custom properties |
| Page-to-View Mapping | `references/page-view-mapping.md` | Mapping multiple raw HTML pages to Laravel views and layout variants |
| Semantic Grouping | `references/semantic-grouping.md` | Identifying navbar, sidebar, card list, form group, empty state, stat card regions |
| Layout & Structure | `references/layout-structure.md` | Setting up master layouts, extracting navbars, headers, sidebars, and footers |
| Blade Components | `references/blade-components.md` | Extracting UI elements using the naming contract, `@props`, `$attributes->merge()`, and slot components |
| Interactivity (Alpine.js) | `references/alpinejs-integration.md` | Adding optional Alpine.js layer for dropdowns, modals, tabs, and toggles |
| Asset Pipeline (Vite) | `references/vite-migration.md` | Migrating from Tailwind CDN to Vite, handling images, icons, fonts, and build verification |
| Accessibility | `references/accessibility.md` | Alt text, aria-labels, heading order, label-input association, modal semantics |
| Diff-Friendly Output | `references/diff-friendly-output.md` | Preserving Tailwind classes, mirroring original structure, minimizing diff noise |
| Validation Checklist | `references/validation-checklist.md` | Checking for duplicate html/head/body, leftover CDN, extracted components, rendering |
| Example Repo Structure | `references/example-repo-structure.md` | Before/after directory structure and file examples |

## Constraints

### MUST DO
- Apply **DRY** (Don't Repeat Yourself) principles. There should not be the same `<!DOCTYPE html>`, `<head>`, or Tailwind CDN link declaration in more than one file.
- Maximize the use of Blade directives (`@extends`, `@section`, `@include`, `@push`, `@stack`, or slot components).
- Maintain HTML semantics and *accessibility*.
- Use `@push('scripts')` or `<x-slot:scripts>` for page-specific *javascript*.
- Convert static CSS display toggles into Alpine.js states where interactivity is needed.
- Migrate styling from CDN to Vite (`@vite(['resources/css/app.css', 'resources/js/app.js'])`).
- Follow the **component naming contract** when extracting components.
- Preserve original Tailwind class strings for diff-friendly output.

### MUST NOT DO
- Leave `<html>`, `<head>`, or `<body>` tags inside specific view files (except for the master layout).
- Repeat the design color configuration (Tailwind config JS) in every view.
- Create a single `.blade.php` file containing thousands of HTML lines without any component extraction.
- Wrap entire pages in `x-data` when only a small region needs interactivity.
- Rewrite or reorder existing Tailwind class strings during extraction.
- Leave CDN scripts (`cdn.tailwindcss.com`) in the converted output.

## Code Templates

Use these as a starting point for conversion.

### Master Layout (`resources/views/layouts/app.blade.php`)

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL=100..700,0..1&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-background text-on-surface font-body-md antialiased">
    <!-- Global Navbar / Sidebar -->
    @include('layouts.partials.navbar')
    @include('layouts.partials.sidebar')

    <!-- Page Specific Content -->
    <main>
        @yield('content')
    </main>

    <!-- Global Footer -->
    @include('layouts.partials.footer')

    <!-- Page Specific Scripts -->
    @stack('scripts')
</body>
</html>
```

### Auth Layout (`resources/views/layouts/auth.blade.php`)

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

### Specific View (`resources/views/pages/dashboard.blade.php`)

```blade
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="max-w-[1280px] mx-auto p-8">
        <h1 class="font-headline-lg text-headline-lg">Welcome</h1>

        <!-- Blade Component Call -->
        <x-stat-card>
            <x-icon name="people" class="text-primary text-[24px]" />
            <div>
                <p class="text-label-md text-on-surface-variant">Total Users</p>
                <p class="text-headline-lg font-headline text-on-surface">1,234</p>
            </div>
        </x-stat-card>

        <x-card>
            <x-button color="primary">Save</x-button>
        </x-card>
    </div>
@endsection

@push('scripts')
<script>
    // Specific JS for the dashboard
</script>
@endpush
```

### Simple Blade Component (`resources/views/components/button.blade.php`)

```blade
@props([
    'variant' => 'primary', // primary, secondary, outline
    'type' => 'button',
    'icon' => null
])

@php
    $baseClasses = 'flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-label-md font-bold transition-all active:scale-[0.98]';
    
    $variantClasses = match($variant) {
        'primary' => 'bg-primary text-on-primary hover:bg-primary-container shadow-md',
        'secondary' => 'bg-secondary text-on-secondary hover:bg-secondary-container',
        'outline' => 'border border-outline-variant text-on-surface hover:bg-surface-container',
        default => 'bg-surface-container text-on-surface',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
    @if($icon)
        <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
    @endif
    
    {{ $slot }}
</button>
```

## Validation Checkpoints

| Stage | Expected Result |
|-------|-----------------|
| Token Extraction | All colors, fonts, and custom CSS are documented and moved to `resources/css/app.css`. |
| Layout Creation | The master layout has the `<html>`, `<head>`, and global script declarations. No CDN links remain. |
| View Integration | Page files (like `dashboard.blade.php`) only contain their semantic inner tags without repeating the `<body>` tag. |
| Component Extraction | Repeated blocks use named components with preserved Tailwind classes. |
| Accessibility | All images have `alt`, icon buttons have `aria-label`, heading order is correct, form labels are associated. |
| Rendering Test | The pages look exactly like the original design but with clean file and folder structures. |
