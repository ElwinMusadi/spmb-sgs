# Page-to-View Mapping Guide

When a design export contains multiple HTML pages (e.g. dashboard.html, login.html, settings.html), map each page to a Laravel view systematically.

## Mapping Protocol

### Step 1: Inventory All Pages

```
exports/
  dashboard.html
  login.html
  settings.html
  create-post.html
```

### Step 2: Identify Shared Chrome

Compare pages to find shared regions:
- Navbar (present in app pages, absent in auth pages)
- Sidebar (present in dashboard/app, absent in login)
- Footer (present in app pages)
- CDN / Head blocks (identical or near-identical)

### Step 3: Create Chrome Partials

Create `resources/views/layouts/partials/`:

```
resources/views/layouts/partials/
  navbar.blade.php
  sidebar.blade.php
  footer.blade.php
```

Only extract a partial if it appears in **2 or more** pages. Otherwise, keep it in the page view.

### Step 4: Choose Layout Variants

Single layout is not always correct. Use multiple layouts for clearly different experiences:

| Page Type | Layout | Shared Regions |
|-----------|--------|----------------|
| App main (dashboard, settings, create) | `layouts.app` | Navbar + Sidebar + Footer |
| Auth (login, register, forgot-password) | `layouts.auth` | Minimal (centered card, auth navbar) |
| Marketing / Public | `layouts.landing` | Navbar + Footer, no sidebar |

```
resources/views/layouts/
  app.blade.php     # App shell with sidebar
  auth.blade.php    # Centered card layout for auth pages
  landing.blade.php # Full-width public pages
```

### Step 5: View Directory Structure

Organize views by feature module:

```
resources/views/
  layouts/
    app.blade.php
    auth.blade.php
    partials/
      navbar.blade.php
      sidebar.blade.php
      footer.blade.php

  pages/
    dashboard.blade.php
    settings.blade.php
    create-post.blade.php

  auth/
    login.blade.php
    register.blade.php
```

### Step 6: Page Migration Checklist

For each page:

1. [ ] Remove `<!DOCTYPE html>`, `<html>`, `<head>`, `<body>`
2. [ ] Remove shared navbar / sidebar if using layout partials
3. [ ] Add `@extends('layouts.XXX')`
4. [ ] Add `@section('title', 'Page Name')`
5. [ ] Wrap content in `@section('content') ... @endsection`
6. [ ] Push page-specific scripts to `@push('scripts')`
7. [ ] Replace raw HTML with Blade components where applicable

### Step 7: Route Alignment

New views should align with intended Laravel routes:

```
Route::get('/dashboard', fn () => view('pages.dashboard'))->middleware('auth');
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/settings', fn () => view('pages.settings'))->middleware('auth');
Route::get('/posts/create', fn () => view('pages.create-post'))->middleware('auth');
```
