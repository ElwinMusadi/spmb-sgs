# Validation Checklist

Use this checklist before declaring a conversion complete. Each stage has specific pass/fail criteria.

## Stage 1: Structural Integrity

| Check | Pass Criteria | Fail Indicator |
|-------|---------------|----------------|
| Single `<!DOCTYPE>` | Only one `<!DOCTYPE html>` in entire project | Multiple `<!DOCTYPE>` strings found |
| Single `<html>` root | Only `layouts/app.blade.php` (or equivalent) contains `<html>` | View files contain `<html>` tags |
| Single `<head>` | Only the master layout contains `<head>` | View files contain `<head>` tags |
| Single `<body>` | Only the master layout contains `<body>` | View files contain `<body>` tags |
| Vite present | `@vite(...)` in master layout `<head>` | `cdn.tailwindcss.com` script remains anywhere |
| CDN removed | Zero occurrences of `cdn.tailwindcss.com` | Any CDN script or link tag for Tailwind |

## Stage 2: Layout Hygiene

| Check | Pass Criteria | Fail Indicator |
|-------|---------------|----------------|
| Partials reused | `@include('layouts.partials.navbar')` used in all app pages | Navbar HTML copied into individual views |
| Footer reused | Footer present only once in `layouts.partials.footer` | Footer HTML repeated |
| Sidebar conditional | Sidebar included only in layout variant that needs it | Sidebar forced into auth pages |

## Stage 3: Component Hygiene

| Check | Pass Criteria | Fail Indicator |
|-------|---------------|----------------|
| No mega-files | No view file exceeds 200 lines of HTML (excluding Blade directives) | Single `.blade.php` with 500+ lines |
| Repeated patterns extracted | 3+ identical card wrappers → component | Same `<div class="bg-...">` repeated 6+ times inline |
| Component props used | Dynamic variants use `@props` + `match`, not inline classes | Every button has a unique hardcoded class string |
| `@attributes->merge()` used | Component root uses `$attributes->merge(['class' => '...'])` | Component root has hardcoded class with no merge |

## Stage 4: Token Integrity

| Check | Pass Criteria | Fail Indicator |
|-------|---------------|----------------|
| `tailwind.config` / `@theme` exists | Token definitions present in config or CSS | Hardcoded colors like `bg-[#00288e]` remaining |
| Fonts loaded | Google Fonts links in layout, not in views | Font `<link>` tags scattered in view files |
| Custom CSS moved | All `<style>` blocks in raw HTML moved to `resources/css/app.css` | Inline `<style>` remaining in Blade views |

## Stage 5: Accessibility Pass

| Check | Pass Criteria | Fail Indicator |
|-------|---------------|----------------|
| `alt` attributes | All `<img>` tags have `alt` | `<img>` without `alt` |
| Icon buttons labeled | Icon-only buttons have `aria-label` | Icon-only button without accessible name |
| Heading order | Single `<h1>`, no skipped levels | `<h1>` followed directly by `<h3>` |
| Label-input association | Every input has a matching `<label for>` | Input without associated label |
| Modal semantics | Modals have `role="dialog"` + `aria-modal` | Modal overlay without ARIA role |

## Stage 6: Alpine.js Separation (if applicable)

| Check | Pass Criteria | Fail Indicator |
|-------|---------------|----------------|
| Interactivity isolated | Alpine `x-data` scoped to interactive regions | `x-data` on static page wrapper |
| `x-cloak` / `display:none` | Interactive toggles prevent flicker | Dropdown visible before Alpine mounts |
| No inline JS | No `onclick="..."` in views | Inline `onclick` handlers remaining |

## Stage 7: Rendering Verification

| Check | Pass Criteria | Fail Indicator |
|-------|---------------|----------------|
| Page renders | `php artisan serve` + view each page in browser | 500 error, missing assets, or layout break |
| Assets load | Vite HMR or `npm run build` succeeds | 404 on CSS/JS, fonts not loading |
| Styles match | Pixel comparison against original raw HTML export | Colors, spacing, or typography visibly different |

## Automated Checks

Run these before submitting:

```bash
# Blade syntax
php artisan view:clear && php artisan view:cache

# Vite build
npm run build

# Optional: Lint
npx prettier --check resources/views/
npx stylelint resources/css/app.css
```

## Common Failure Patterns

| Failure | Cause | Fix |
|---------|-------|-----|
| Tailwind classes not applying | CDN still in layout alongside Vite | Remove `cdn.tailwindcss.com` |
| Double body padding | Layout + view both have container padding | Move container to layout, remove from views |
| Missing fonts | Font `<link>` in view instead of layout | Move to `layouts.app` `<head>` |
| Broken icons | Material Symbols stylesheet missing | Add font link back to layout |
| Dark mode broken | `class="light"` not preserved on `<html>` | Add `class="light"` back to layout |
