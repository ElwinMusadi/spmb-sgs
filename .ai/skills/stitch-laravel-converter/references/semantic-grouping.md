# Semantic Grouping Guide

Group raw HTML elements into semantic regions before extracting components. This reduces extraction noise and helps produce diff-friendly output.

## Region Heuristics

Classify each top-level block in the page body into one of these categories:

| Region | Indicators | Extraction Priority |
|--------|------------|---------------------|
| **Navbar / Header** | Fixed/sticky positioning, logo, nav links, user avatar | Extract as `layouts.partials.navbar` |
| **Sidebar** | Vertical nav, collapsible panels, icon-only labels | Extract as `layouts.partials.sidebar` |
| **Footer** | Bottom of page, copyright, secondary links | Extract as `layouts.partials.footer` |
| **Table / Card List** | Repeated `<tr>` or card blocks with similar class structure | Extract as `<x-table>` or `<x-card-grid>` |
| **Form Group** | `<form>` or grouped inputs with labels and submit | Extract as `<x-form>` or `input-group` inside form |
| **Empty State** | Centered icon + text, no data indicator | Extract as `<x-empty-state>` |
| **Stat Card** | Icon + number + label, summary metrics | Extract as `<x-stat-card>` |
| **Modal / Drawer** | Fixed overlay, `z-50`, close button | Keep inline or extract with Alpine |
| **Search / Filter Bar** | Input + dropdowns + button row above content | Extract as `<x-search-bar>` |

## Detection Rules

### Navbar / Header

Look for:
- `class="fixed top-0 ..."`, `h-16`, `h-14` heights
- Logo image or text + navigation links in a single row
- User menu

Extract into `resources/views/layouts/partials/navbar.blade.php` if appears in 2+ pages.

### Sidebar

Look for:
- `w-64`, `w-56`, `w-72` fixed width
- Vertical stacking of links with `flex flex-col`
- Icons + label pattern (often with `gap-2`)

Extract into `resources/views/layouts/partials/sidebar.blade.php` if appears in 2+ pages.

### Table / Card List

Look for:
- Multiple identical wrappers: `<div class="bg-surface-container-lowest p-6 rounded-xl">`
- Repeated table rows with identical cell structure
- `grid grid-cols-1 md:grid-cols-3` repeating block pattern

Extract into `resources/views/components/stat-card.blade.php` or `resources/views/components/data-table.blade.php`.

### Form Group

Look for:
- `<form>` wrapping label + input + error block
- Repeating `flex flex-col gap-2` pattern
- `text-error` for validation messages

Extract into `resources/views/components/input-group.blade.php` or `resources/views/components/form-field.blade.php`.

### Empty State

Look for:
- Large icon or illustration inside `flex flex-col items-center justify-center py-12`
- Message text + optional secondary action button

Extract into `resources/views/components/empty-state.blade.php`.

## Grouping Decision Tree

```
Body content
├── Fixed/sticky top bar with logo/nav?
│   └── YES → Navbar (partial)
├── Vertical column on left (w-64)?
│   └── YES → Sidebar (partial)
├── Repeated block (3+ times)?
│   ├── Contains icon + number + label?
│   │   └── YES → Stat Card (component)
│   ├── Contains image/icon + title + description?
│   │   └── YES → Card (component)
│   └── Table-like with columns?
│       └── YES → Data Table (component)
├── Form with labels and inputs?
│   └── YES → Form (section, use input-group components inside)
├── Centered icon + message + no data?
│   └── YES → Empty State (component)
└── Bottom of page?
    └── YES → Footer (partial)
```

## Output Preservation Rule

When extracting regions, do NOT restructure the existing Tailwind classes. Keep them exactly as found in the raw HTML to support diff-friendly output. Example:

**Original:**
```html
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant form-shadow">
  <div class="flex items-center gap-4">
    <span class="material-symbols-outlined text-[24px] text-primary">dashbaord</span>
    <div>
      <p class="text-label-md text-on-surface-variant">Total Users</p>
      <p class="text-headline-lg font-headline text-on-surface">1,234</p>
    </div>
  </div>
</div>
```

**Component (preserved):**
```blade
<div {{ $attributes->merge(['class' => 'bg-surface-container-lowest p-6 rounded-xl border border-outline-variant form-shadow']) }}>
  {{ $slot }}
</div>
```

**Usage:**
```blade
<x-stat-card>
  <x-icon name="dashboard" class="text-primary text-[24px]" />
  <div>
    <p class="text-label-md text-on-surface-variant">Total Users</p>
    <p class="text-headline-lg font-headline text-on-surface">1,234</p>
  </div>
</x-stat-card>
```
