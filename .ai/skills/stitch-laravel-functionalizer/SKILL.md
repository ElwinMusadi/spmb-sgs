---
name: stitch-laravel-functionalizer
description: Brings static UI designs (Blade/HTML) to life by connecting them to the Laravel backend. Includes data modeling, routing, controllers, form handling, and injecting dynamic data into Blade views. Use this when the user asks to "make the design functional", "connect to database", or "add backend logic to the UI".
license: MIT
metadata:
  version: "1.1.0"
  domain: fullstack
  triggers: functionalize design, connect to database, add logic to UI, make forms work, dynamic data, stitch backend integration
  role: fullstack engineer
  scope: implementation
  output-format: code
---

# Stitch to Laravel Functionalizer

Specialist in bridging the gap between static Frontend UI designs and Laravel Backend architecture. This skill ensures that static mockups are correctly transformed into fully functioning data-driven applications.

## Core Workflow

1. **Data Mapping & Schema Design** — Analyze the static view to identify required entities (e.g., if there's a list of courses, you need a `Course` model). Map the UI fields to database columns.
2. **Backend Infrastructure** — Create necessary Migrations and Eloquent Models. Define `$fillable` properties, casts, and relationships (`HasMany`, `BelongsTo`, etc.).
3. **Routing & Middleware** — Register appropriate routes (GET, POST, PUT, DELETE) in `routes/web.php` or `routes/api.php`. Apply necessary middleware (like `auth` or `verified`).
4. **Controller Implementation** — Create Controllers to handle the business logic. Implement data fetching (using eager loading to prevent N+1 issues).
5. **Authorization & Policies** — Define Laravel Policies or Gates based on user roles. Enforce authorization in Controllers and in Blade views using `@can` / `@cannot` directives to hide restricted actions.
6. **View Data Binding** — Replace dummy static text and hardcoded lists in the `.blade.php` files with dynamic variables (`{{ $item->name }}`) and loops (`@forelse`, `@foreach`). Handle empty states gracefully.
7. **Form Integration & Feedback** — Update HTML `<form>` tags with `@csrf`, proper `method`, and `action="{{ route(...) }}"`. Add `@error` blocks for validation. Ensure Controllers return session flash messages (`redirect()->with('success', ...)`) and the UI renders them (e.g. as Toasts).

## Constraints

### MUST DO

- **Always validate user input**, preferably using dedicated **Form Request classes** (`php artisan make:request`) to keep Controllers thin.
- **Eager load relationships** when passing collections to the view to avoid N+1 query problems.
- Use **descriptive variable names** when passing data to views (e.g., use `$courses` instead of a generic `$data`).
- Handle **empty states** in the UI (e.g., using `@forelse` to show a "No data available" message if a collection is empty).
- Retain the original HTML/Tailwind classes and styling when injecting dynamic data.
- **Create Factories & Seeders** immediately after defining a model so the UI doesn't look empty when tested.

### MUST NOT DO

- **Do not write database queries directly inside `.blade.php` files.** All data retrieval must happen in the Controller.
- **Do not ignore Mass Assignment protection.** Always define `$fillable` or `$guarded` in Models.
- **Do not remove or break existing Tailwind classes** when converting static HTML to dynamic Blade loops.
- **Do not leave static "dummy" data** behind once the backend is connected.

## Reference Guide

Load detailed guidance based on context:

| Topic                | Reference                            | Load When                                                                                |
| -------------------- | ------------------------------------ | ---------------------------------------------------------------------------------------- |
| Backend Architecture | `references/backend-architecture.md` | Creating Models, Migrations, Controllers, and defining Routes                            |
| View Data Binding    | `references/data-binding.md`         | Mapping dynamic Eloquent data to HTML tables, grids, or lists                            |
| Form Handling        | `references/form-handling.md`        | Updating static HTML forms with proper Laravel CSRF, routing, and validation display     |
| Mockup Data          | `references/mockup-data-seeding.md`  | Creating Factories and Seeders to populate the database with data matching the static UI |

## Code Templates

Use these as a starting point for functionalizing views.

### Controller Data Fetching

```php
public function index()
{
    // Eager load relationships to prevent N+1
    $courses = Course::with('category', 'instructor')
                     ->where('status', 'published')
                     ->latest()
                     ->paginate(12);

    return view('elearning.courses.index', compact('courses'));
}
```

### View Data Binding (Lists)

```blade
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @forelse($courses as $course)
        <x-course-card :course="$course" />
    @empty
        <div class="col-span-3 text-center py-12 text-on-surface-variant">
            <span class="material-symbols-outlined text-4xl mb-4">search_off</span>
            <p>No courses available at the moment.</p>
        </div>
    @endforelse
</div>
```

### Form Integration

```blade
<form action="{{ route('courses.store') }}" method="POST" class="flex flex-col gap-4">
    @csrf

    <div>
        <label for="title" class="font-bold">Course Title</label>
        <input type="text" name="title" id="title" value="{{ old('title') }}"
               class="border rounded px-4 py-2 w-full @error('title') border-error @enderror">

        @error('title')
            <p class="text-error text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="bg-primary text-white rounded px-6 py-2">
        Save Course
    </button>
</form>
```

## Validation Checkpoints

| Stage          | Expected Result                                                                                              |
| -------------- | ------------------------------------------------------------------------------------------------------------ |
| Data Mapping   | All static UI elements (tables, metrics, lists) have a corresponding database representation.                |
| Form Submit    | Submitting a form successfully saves data to the DB and redirects with a success message.                    |
| Form Errors    | Submitting an empty/invalid form shows Laravel validation errors directly on the UI without breaking layout. |
| Rendering Test | The page looks exactly like the static design, but all data is pulled from the database dynamically.         |
