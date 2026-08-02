# Diff-Friendly Output Guide

Diff-friendly output means the converted Blade files should be easy to review in a code review, with minimal changes from the original raw HTML structure.

## Principles

1. **Preserve original Tailwind class strings**: Do not reorder, compress, or rewrite class attributes.
2. **Mirror original nesting**: Keep the same DOM depth and wrapping order.
3. **Minimize structural changes**: Extract components without changing layout behavior.
4. **Use `@attributes->merge()` for class injection**: This appends new classes instead of replacing the entire attribute.

## Class Preservation Rules

### WRONG — Destructive class rewrite

```blade
{{-- Bad: rewrites all classes --}}
<div {{ $attributes->merge(['class' => 'p-6 rounded-xl border bg-white']) }}>
```

### CORRECT — Merge preserves original classes

```blade
{{-- Good: preserves original, appends new --}}
<div {{ $attributes->merge(['class' => 'bg-surface-container-lowest p-6 rounded-xl border border-outline-variant form-shadow']) }}>
```

## Structural Preservation

When extracting a repeated block into a component, keep the same internal structure:

**Original:**
```html
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant form-shadow">
    <div class="flex items-center gap-4">
        <span class="material-symbols-outlined text-[24px] text-primary">dashboard</span>
        <div>
            <p class="text-label-md text-on-surface-variant">Total Users</p>
            <p class="text-headline-lg font-headline text-on-surface">1,234</p>
        </div>
    </div>
</div>
```

**Component (preserves structure):**
```blade
<div {{ $attributes->merge(['class' => 'bg-surface-container-lowest p-6 rounded-xl border border-outline-variant form-shadow']) }}>
    {{ $slot }}
</div>
```

**Usage:**
```blade
<x-stat-card>
    <div class="flex items-center gap-4">
        <span class="material-symbols-outlined text-[24px] text-primary">dashboard</span>
        <div>
            <p class="text-label-md text-on-surface-variant">Total Users</p>
            <p class="text-headline-lg font-headline text-on-surface">1,234</p>
        </div>
    </div>
</x-stat-card>
```

## Component Naming for Diff Clarity

Use generic, descriptive component names so diffs stay readable:

| Pattern | Bad Name | Good Name |
|---------|----------|-----------|
| Stat cards | `<x-user-card>` | `<x-stat-card>` |
| Data rows | `<x-row>` | `<x-data-row>` |
| Filter bars | `<x-bar>` | `<x-filter-bar>` |
| Icon buttons | `<x-btn>` | `<x-icon-button>` |
| Form wrappers | `<x-wrap>` | `<x-form-field>` |

## Indentation and Formatting

- Keep 4-space indentation for Blade
- Keep attributes multi-line when they would exceed 120 chars in a single line
- Do not auto-format or reorder attributes

**Before:**
```html
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant form-shadow hover:shadow-md transition-all">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <span class="material-symbols-outlined text-[24px] text-primary">dashboard</span>
        </div>
        <div>
            <p class="text-label-md text-on-surface-variant">Total Users</p>
            <p class="text-headline-lg font-headline text-on-surface">1,234</p>
        </div>
    </div>
</div>
```

**After (componentized but structure preserved):**
```blade
<x-stat-card class="hover:shadow-md transition-all">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <x-icon name="dashboard" class="text-primary text-[24px]" />
        </div>
        <div>
            <p class="text-label-md text-on-surface-variant">Total Users</p>
            <p class="text-headline-lg font-headline text-on-surface">1,234</p>
        </div>
    </div>
</x-stat-card>
```

## Line Count Preservation

Avoid collapsing multi-line content into single lines during extraction. The diff should show:

- Lines removed: mostly shared chrome and repeated structure
- Lines added: `@extends`, `@section`, component tags
- Lines unchanged: all inner Tailwind class strings

This makes the review focus on architecture, not content changes.
