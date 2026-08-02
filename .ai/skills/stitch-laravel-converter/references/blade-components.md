# Blade Components Guide

Extracting repeated UI elements into Blade Components (`resources/views/components/`) makes your codebase cleaner, smaller, and easier to maintain.

## 1. Component Naming Contract

Use consistent, semantic names so the codebase is scannable and diffs remain readable.

| Pattern | Component Name | Directory |
|---------|---------------|-----------|
| Button (with variants) | `button` | `components/` |
| Input with label + error | `input-group` | `components/` |
| Stat card (icon + number + label) | `stat-card` | `components/` |
| Card container | `card` | `components/` |
| Icon (Material Symbols) | `icon` | `components/` |
| Data table with headers | `data-table` | `components/` |
| Empty state (no data) | `empty-state` | `components/` |
| Search / filter bar | `search-bar` | `components/` |
| Form section wrapper | `form-field` | `components/` |
| Alert / notification | `alert` | `components/` |
| Modal overlay | `modal` | `components/` |
| Dropdown menu | `dropdown` | `components/` |

### Naming Rules

- Use kebab-case: `stat-card`, not `StatCard` or `stat_card`
- Use noun phrases: `data-table`, not `table-data`
- Avoid generic names: `box`, `wrapper`, `panel` → prefer semantic: `stat-card`, `form-section`
- Do not name components after specific data: `user-card` → `stat-card` (unless data is truly domain-specific)

## 2. Anonymous Blade Components

For most UI elements (buttons, inputs, cards), Anonymous Blade Components are the best choice. They don't require a PHP class, just a `.blade.php` file in the `components` directory.

### Example: Card Component (`resources/views/components/card.blade.php`)

Raw HTML typically looks like this, repeated many times:
```html
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant form-shadow">
    <!-- Content -->
</div>
```

**Component Definition (preserves classes):**
```blade
<div {{ $attributes->merge(['class' => 'bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm']) }}>
    {{ $slot }}
</div>
```

**Usage in View:**
```blade
<x-card class="mb-4">
    <h2 class="text-xl">Card Title</h2>
    <p>Card content goes here.</p>
</x-card>
```

## 3. Using `@props` for Dynamic Classes

When an element has variations (like button colors), use `@props` to define defaults and handle the logic.

### Example: Button Component (`resources/views/components/button.blade.php`)

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

**Usage:**
```blade
<x-button variant="primary" type="submit" icon="save">
    Save Changes
</x-button>

<x-button variant="outline" href="{{ route('cancel') }}">
    Cancel
</x-button>
```

## 4. Form Input Components

Form inputs often have repeated structures (label, input, error message).

### Example: Input Group (`resources/views/components/input-group.blade.php`)

```blade
@props([
    'name',
    'label',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'value' => null,
    'error' => null,
    'ariaDescribedBy' => null
])

@php
    $errorId = $name . '-error';
    $describedBy = $ariaDescribedBy ?: ($error ? $errorId : null);
@endphp

<div class="flex flex-col gap-2">
    <label class="font-label-md text-on-surface ml-1" for="{{ $name }}">
        {{ $label }}
        @if($required) <span class="text-error" aria-hidden="true">*</span> @endif
    </label>
    
    <div class="relative">
        <input 
            type="{{ $type }}" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            value="{{ old($name, $value) }}" 
            placeholder="{{ $placeholder }}" 
            {{ $required ? 'required' : '' }}
            {{ $describedBy ? 'aria-describedby="' . $describedBy . '"' : '' }}
            {{ $attributes->merge(['class' => 'w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface focus:border-primary focus:ring-1 focus:ring-primary transition-all outline-none']) }}
        />
    </div>
    
    @if($error)
        <p id="{{ $errorId }}" class="text-error text-[12px] ml-1" role="alert">{{ $error }}</p>
    @endif
</div>
```

**Usage:**
```blade
<x-input-group 
    name="email" 
    label="Email Address" 
    type="email" 
    placeholder="john@example.com" 
    required="true" 
    :error="$errors->first('email')"
/>
```

## 5. Icons

Instead of writing out the full Material Symbols span every time, create an icon component.

### Example: Icon (`resources/views/components/icon.blade.php`)

```blade
@props(['name', 'weight' => '400', 'fill' => '0'])

<span 
    {{ $attributes->merge(['class' => 'material-symbols-outlined']) }} 
    style="font-variation-settings: 'FILL' {{ $fill }}, 'wght' {{ $weight }};"
>
    {{ $name }}
</span>
```

**Usage:**
```blade
<x-icon name="dashboard" class="text-primary text-[24px]" />
```

## 6. Diff-Friendly Extraction

When extracting a component, preserve the original Tailwind class strings exactly as found in the raw HTML. Do not reorder or rewrite classes.

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

**Component:**
```blade
<div {{ $attributes->merge(['class' => 'bg-surface-container-lowest p-6 rounded-xl border border-outline-variant form-shadow']) }}>
    {{ $slot }}
</div>
```

**Usage:**
```blade
<x-stat-card>
    <div class="flex items-center gap-4">
        <x-icon name="dashboard" class="text-primary text-[24px]" />
        <div>
            <p class="text-label-md text-on-surface-variant">Total Users</p>
            <p class="text-headline-lg font-headline text-on-surface">1,234</p>
        </div>
    </div>
</x-stat-card>
```
