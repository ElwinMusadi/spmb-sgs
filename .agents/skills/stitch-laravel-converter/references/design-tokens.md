# Design Token Extraction Guide

Extract all design tokens from raw Tailwind CDN config systematically before writing any Blade code. This ensures consistent styling across the entire application.

## Token Catalog

Parse the raw HTML for all of the following contexts:

| Token Type | Look For | Target Location |
|------------|----------|-----------------|
| **Colors** | `theme.extend.colors` in `<script id="tailwind-config">` and any inline `bg-`, `text-`, `border-` classes | CSS custom properties, component props |
| **Fonts** | `<link href="https://fonts.googleapis.com/...">` in `<head>` and `font-*` classes (e.g. `font-body-md`, `font-headline-lg`) | CSS `@theme`, font-family utilities |
| **Spacing/Sizing** | Consistent patterns like `p-8`, `max-w-[1280px]`, `h-16`, `w-64` | Can be preserved as-is in Tailwind |
| **Shadows** | `shadow-md`, `shadow-sm`, custom CSS shadow values | `box-shadow` token or preserve utility |
| **Border Radius** | `rounded-xl`, `rounded-full`, `rounded-lg` | Preserve as utility or token |
| **Typography Scale** | `text-headline-lg`, `font-label-md`, `font-body-md` | CSS custom properties mapping to font-size/weight/line-height |
| **Z-Index** | `z-50`, `z-40`, `z-10` | Preserve utilities |

## Extraction Steps

### Step 1: Extract Tailwind Config Script

Find the `<script id="tailwind-config">` block:

```html
<script id="tailwind-config">
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: "#00288e",
          secondary: "#00897b",
          "on-primary": "#ffffff",
          outline: "#e0e0e0",
          "surface-container": "#f5f5f5",
          "background": "#fefefe",
          "on-surface": "#1c1b1f",
          error: "#b3261e"
        },
        fontFamily: {
          headline: ["Inter", "sans-serif"],
          body: ["Inter", "sans-serif"],
          label: ["Inter", "sans-serif"]
        }
      }
    }
  }
</script>
```

### Step 2: Extract Font Imports

Find `<link>` tags for Google Fonts or other font services:

```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
```

### Step 3: Extract Custom CSS

Find any `<style>` blocks with custom scrollbars, animations, or overrides:

```html
<style>
  .custom-scrollbar::-webkit-scrollbar { width: 6px; }
  .form-shadow { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
</style>
```

### Step 4: Map to Tailwind v4 CSS-First Config

For Tailwind v4 projects, translate tokens into `resources/css/app.css`:

```css
@import "tailwindcss";

@theme {
  /* Colors */
  --color-primary: #00288e;
  --color-secondary: #00897b;
  --color-on-primary: #ffffff;
  --color-outline: #e0e0e0;
  --color-surface-container: #f5f5f5;
  --color-background: #fefefe;
  --color-on-surface: #1c1b1f;
  --color-error: #b3261e;

  /* Typography scale */
  --font-headline: Inter, sans-serif;
  --font-body: Inter, sans-serif;
  --font-label: Inter, sans-serif;
  --text-headline-lg: 2rem;
  --text-headline-md: 1.5rem;
  --text-body-md: 0.875rem;
  --text-label-md: 0.75rem;
}
```

### Step 5: For Tailwind v3 (Legacy)

If the project uses Tailwind v3, generate `tailwind.config.js` from the extracted tokens:

```js
/** @type {import('tailwindcss').Config} */
export default {
  theme: {
    extend: {
      colors: {
        primary: '#00288e',
        secondary: '#00897b',
        'on-primary': '#ffffff',
        // ...
      },
      fontFamily: {
        headline: ['Inter', 'sans-serif'],
        body: ['Inter', 'sans-serif'],
        label: ['Inter', 'sans-serif'],
      },
      boxShadow: {
        'form': '0 2px 8px rgba(0,0,0,0.08)',
      },
    },
  },
}
```

### Step 6: Preserve Existing Class Patterns

Keep using the same Tailwind utility classes in Blade output. Do not rip out and replace `bg-primary`, `text-on-surface`, `max-w-[1280px]`, `p-8`, etc. — these should remain untouched in the generated Blade files so diffs stay readable.

## Quick Token Reference Sheet

After extraction, build a quick reference to avoid inventing tokens mid-conversion:

```
Colors: primary #00288e, secondary #00897b, on-primary #fff, outline #e0e0e0, surface-container #f5f5f5, background #fefefe, on-surface #1c1b1f, error #b3261e
Fonts: Inter (headline, body, label), Material Symbols Outlined (icons)
Radius: rounded-xl, rounded-full (defaults)
Spacing: p-8 (page), p-6 (card), p-4 (compact)
```
