# Alpine.js Integration Guide

Static HTML designs often rely on CSS hover states or simply show all elements expanded (like open modals or dropdowns) to demonstrate the UI. To make these functional without writing complex vanilla JS, use Alpine.js.

**Important**: Alpine.js is optional. Only add interactivity to regions that genuinely need it (dropdowns, modals, tabs, accordions). Do not wrap static content in `x-data` unnecessarily.

## 1. Setup

Ensure Alpine.js is included in your `layouts.app` or `resources/js/app.js` (via Vite).

**`resources/js/app.js`:**
```js
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

## 2. Dropdowns & Menus

Static designs often display the dropdown menu openly. We need to hide it by default and toggle it on click.

**Static HTML:**
```html
<div class="relative">
    <button>Profile</button>
    <div class="absolute right-0 mt-2 bg-white shadow-lg">
        <a href="#">Settings</a>
        <a href="#">Logout</a>
    </div>
</div>
```

**Alpine.js Version:**
```blade
<div x-data="{ open: false }" @click.away="open = false">
    <button 
        @click="open = !open" 
        aria-label="User menu"
        aria-expanded="open"
        aria-controls="user-menu"
    >
        <x-icon name="person" class="text-[24px]" />
    </button>
    
    <div 
        id="user-menu"
        x-show="open" 
        x-transition
        class="absolute right-0 mt-2 bg-white shadow-lg rounded-xl border border-outline-variant py-2 min-w-[200px]"
        style="display: none;"
    >
        <a href="#" class="block px-4 py-2 hover:bg-surface-container">Settings</a>
        <a href="#" class="block px-4 py-2 hover:bg-surface-container">Logout</a>
    </div>
</div>
```

*(Note: Always add `style="display: none;"` or `x-cloak` to prevent UI flickering before Alpine initializes).*

## 3. Modals

Modals in static designs are usually placed at the bottom of the HTML and use inline styles or Tailwind classes like `hidden` or `flex` to show/hide them.

**Static HTML:**
```html
<!-- Button somewhere -->
<button onclick="document.getElementById('modal').style.display='flex'">Open Modal</button>

<!-- Modal -->
<div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center">
    <div class="bg-white p-6">
        <h2>Modal Title</h2>
        <button onclick="document.getElementById('modal').style.display='none'">Close</button>
    </div>
</div>
```

**Alpine.js Version:**
Instead of scattering `onclick` events, bind the modal to Alpine state. Often, this state is best placed at the parent component or managed via Alpine `$dispatch`.

```blade
<div x-data="{ showModal: false }">
    <!-- Trigger -->
    <button @click="showModal = true" class="...">Open Modal</button>

    <!-- Modal -->
    <div 
        x-show="showModal" 
        x-trap="showModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center"
        style="display: none;"
    >
        <!-- Backdrop click to close -->
        <div class="absolute inset-0" @click="showModal = false"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white p-6 z-10 rounded-xl" @click.stop>
            <h2 id="modal-title">Modal Title</h2>
            <button @click="showModal = false">Close</button>
        </div>
    </div>
</div>
```

## 4. Tabs

Tabs are another common UI element that needs to be functionalized.

**Alpine.js Version:**
```blade
<div x-data="{ activeTab: 'tab1' }" role="tablist">
    <!-- Tab Headers -->
    <div class="flex border-b">
        <button 
            @click="activeTab = 'tab1'" 
            role="tab"
            :aria-selected="activeTab === 'tab1'"
            :class="activeTab === 'tab1' ? 'border-b-2 border-primary text-primary' : 'text-gray-500'"
            class="px-4 py-2"
        >Tab 1</button>
        <button 
            @click="activeTab = 'tab2'" 
            role="tab"
            :aria-selected="activeTab === 'tab2'"
            :class="activeTab === 'tab2' ? 'border-b-2 border-primary text-primary' : 'text-gray-500'"
            class="px-4 py-2"
        >Tab 2</button>
    </div>

    <!-- Tab Contents -->
    <div class="p-4">
        <div 
            x-show="activeTab === 'tab1'" 
            role="tabpanel"
        >Content for Tab 1</div>
        <div 
            x-show="activeTab === 'tab2'" 
            role="tabpanel"
            style="display: none;"
        >Content for Tab 2</div>
    </div>
</div>
```

## 5. Optional Layer — Separate Interactions

Keep Alpine.js concerns separated from structural markup:

### WRONG — Alpine on page wrapper
```blade
{{-- Bad: binds entire page to dropdown state --}}
<div x-data="{ open: false }">
    @include('layouts.partials.navbar')
    <main>
        <!-- 500 lines of static content -->
    </main>
</div>
```

### CORRECT — Alpine scoped to interactive region
```blade
{{-- Good: Alpine only on the dropdown wrapper --}}
@include('layouts.partials.navbar')
<main>
    <!-- 500 lines of static content -->
</main>

{{-- Dropdown gets its own x-data --}}
<div x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open">...</button>
    <div x-show="open" style="display: none;">...</div>
</div>
```

### When to Use Alpine vs. Inline

| Pattern | Action |
|---------|--------|
| Dropdown / Menu | Use Alpine `x-data` on wrapper |
| Modal / Dialog | Use Alpine `x-show` + `x-trap` |
| Tabs / Accordion | Use Alpine `x-data` with state variable |
| Toggle / Switch | Use Alpine `x-model` |
| Static hover effect | Keep as CSS (`hover:`, `group-hover:`) |
| One-time inline click | Prefer `<a href>` or `<button>` without Alpine |
