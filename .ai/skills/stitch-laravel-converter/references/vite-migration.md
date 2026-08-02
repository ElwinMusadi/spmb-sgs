# Vite Asset Migration Guide

Static UI exports often use Tailwind CSS via a CDN (`<script src="https://cdn.tailwindcss.com"></script>`). This is unacceptable for production Laravel applications. Migrate the styling to Laravel's Vite build system.

## Step 1: Extract Design Tokens from CDN Config

Find the `<script id="tailwind-config">` block in the raw HTML.

**Raw HTML:**
```html
<script id="tailwind-config">
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: "#00288e"
                }
            }
        }
    }
</script>
```

**Action for Tailwind v4:**
Translate `theme.extend` tokens into CSS `@theme` declarations in `resources/css/app.css`.

**`resources/css/app.css`:**
```css
@import "tailwindcss";

@theme {
    --color-primary: #00288e;
}

/* custom styles */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
```

If the project uses a different Tailwind major version, keep the JS config path instead.

## Step 2: Extract Custom CSS

Find any `<style>` blocks in the raw HTML. These usually contain custom scrollbars, font imports, or specific overrides.

**Raw HTML:**
```html
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
</style>
```

**Action:**
Move these styles into `resources/css/app.css`.

**`resources/css/app.css`:**
```css
@import "tailwindcss";

@theme {
    --color-primary: #00288e;
}

@layer utilities {
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
}
```

## Step 3: Asset Pipeline (Images, Icons, Fonts)

### Copy Static Assets

Move all static assets from the raw export into `resources/`:

```
exports/
  images/
    logo.png
    avatar.png
    chart.png
  icons/
    dashboard.svg
    settings.svg

resources/
  images/
    logo.png        # copied from exports/images/
    avatar.png
    chart.png
  icons/
    dashboard.svg   # copied from exports/icons/
    settings.svg
```

### Font Imports

Keep font `<link>` tags in the master layout `<head>`. Do NOT move them to views.

```blade
<head>
    <!-- ... -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL=100..700,0..1&display=swap" rel="stylesheet">
</head>
```

### Referencing Images in Blade

Use `Vite::asset()` for images placed in `resources/`:

```blade
<img src="{{ Vite::asset('resources/images/logo.png') }}" alt="Logo" />
```

Or use Laravel's asset helper if the image is in `public/`:

```blade
<img src="{{ asset('images/logo.png') }}" alt="Logo" />
```

### Icon Strategy

For SVG icons in `resources/icons/`, create Blade icon components:

```blade
{{-- resources/views/components/icon.blade.php --}}
@props(['name', 'weight' => '400', 'fill' => '0'])

<span 
    {{ $attributes->merge(['class' => 'material-symbols-outlined']) }} 
    style="font-variation-settings: 'FILL' {{ $fill }}, 'wght' {{ $weight }};"
>
    {{ $name }}
</span>
```

```blade
{{-- Usage --}}
<x-icon name="dashboard" class="text-primary text-[24px]" />
```

### Vite Entry Points

**`vite.config.js`:**
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

**`package.json` scripts:**
```json
{
    "scripts": {
        "dev": "vite",
        "build": "vite build"
    }
}
```

## Step 4: Update the Master Layout

Remove the CDN script and custom `<style>`/`<script>` blocks from your `layouts.app.blade.php`, and replace them with the `@vite` directive.

**Before:**
```blade
<head>
    <!-- ... -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config"> ... </script>
    <style> ... </style>
</head>
```

**After:**
```blade
<head>
    <!-- ... -->
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL=100..700,0..1&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

## Step 5: Verify Build

```bash
npm run build
```

Check that:
- `public/build/assets/` contains compiled CSS and JS
- Tailwind classes are present in the compiled output
- No CDN references remain in the HTML source
