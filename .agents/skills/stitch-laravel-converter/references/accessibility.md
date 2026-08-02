# Accessibility Pass Guide

After converting raw HTML to Blade, run an accessibility pass to ensure the output is usable for all users.

## Audit Checklist

Run through every generated view file and verify:

### Images and Media

- [ ] Every `<img>` has an `alt` attribute
  - Decorative images: `alt=""`
  - Informative images: `alt="Descriptive text"`
- [ ] `<svg>` elements that convey information have `aria-label` or `<title>`
- [ ] Background images via CSS are not conveying critical information

```html
<!-- Before (raw HTML, missing alt) -->
<img src="/img/chart.png">

<!-- After -->
<img src="{{ Vite::asset('resources/images/chart.png') }}" alt="Monthly revenue chart">
```

### Interactive Elements

- [ ] Buttons with only icons have `aria-label`
- [ ] Dropdown triggers have `aria-expanded` (if using Alpine, bind it)
- [ ] Modals have `role="dialog"`, `aria-modal="true"`, and a labeled `aria-labelledby`
- [ ] Tabs use `role="tablist"`, `role="tab"`, `role="tabpanel"`, and `aria-selected`

```blade
<!-- Icon button without text -->
<button @click="open = !open" aria-label="Open user menu" aria-expanded="open">
    <x-icon name="menu" />
</button>
```

### Heading Order

- [ ] Pages start with a single `<h1>`
- [ ] No skipped levels (no `<h3>` directly after `<h1>` without an `<h2>`)
- [ ] Section headings are consistent

```blade
{{-- Correct heading hierarchy --}}
<h1 class="font-headline-lg text-headline-lg">Dashboard</h1>

<section>
    <h2 class="font-headline-md text-headline-md">Recent Activity</h2>
    <!-- content -->
</section>

<section>
    <h2 class="font-headline-md text-headline-md">Statistics</h2>
    <h3 class="font-headline text-headline">This Month</h3>
    <!-- content -->
</section>
```

### Form Labels and Inputs

- [ ] Every `<input>` has a matching `<label>` with `for` attribute matching the input `id`
- [ ] Required fields have `aria-required="true"` or `required` attribute
- [ ] Error messages are linked via `aria-describedby`
- [ ] `<select>`, `<textarea>`, and custom inputs follow the same pattern

```blade
<x-input-group 
    name="email" 
    label="Email Address" 
    type="email" 
    required="true" 
    :error="$errors->first('email')"
    aria-describedby="email-error"
/>

@error('email')
    <p id="email-error" class="text-error text-[12px] ml-1" role="alert">
        {{ $message }}
    </p>
@enderror
```

### Landmarks and Roles

- [ ] Main content area uses `<main>` or `role="main"`
- [ ] Navigation uses `<nav>` or `role="navigation"`
- [ ] Complementary content uses `<aside>` or `role="complementary"`
- [ ] Contentinfo uses `<footer>` or `role="contentinfo"`

### Focus Management

- [ ] Modal focus is trapped (Alpine `x-trap` or manual JS)
- [ ] Focus returns to trigger element when modal closes
- [ ] Dropdowns close on Escape key (Alpine handles this via `@click.away`)

### Color and Contrast

- [ ] Text color pairs against background meet WCAG AA minimum contrast
- [ ] Focus indicators are visible (`focus:ring`, `focus:outline`)
- [ ] Color is not the only indicator of state (use icons + text alongside color for errors)

## Quick Reference: Blade + Alpine Pattern

```blade
<div x-data="{ open: false }">
    <button 
        @click="open = !open" 
        aria-label="Notifications"
        aria-expanded="open"
        aria-controls="notifications-panel"
    >
        <x-icon name="notifications" />
    </button>
    
    <div 
        id="notifications-panel"
        x-show="open" 
        role="region" 
        aria-label="Notifications"
        style="display: none;"
    >
        <!-- notification items -->
    </div>
</div>
```

## Linting Integration

Add to CI / pre-commit if available:

```bash
npx pa11y-ci --sitemap http://localhost/sitemap.xml
```

Or for Blade-specific checks:

```bash
vendor/bin/phpstan analyse --level=max --no-progress resources/views
```
