# View Data Binding Guide

Connecting static HTML lists, grids, and tables to dynamic backend data is one of the most critical steps in functionalizing a design.

## 1. Handling Collections (Grids & Lists)

Static designs often have 3 or 4 hardcoded "cards" to show how a grid looks. You must delete all but one of these cards, and wrap the remaining one in a `@forelse` loop.

**Static HTML:**
```html
<div class="grid grid-cols-3 gap-6">
    <div class="card">Course 1</div>
    <div class="card">Course 2</div>
    <div class="card">Course 3</div>
</div>
```

**Dynamic Blade:**
```blade
<div class="grid grid-cols-3 gap-6">
    @forelse($courses as $course)
        <div class="card">
            <h3>{{ $course->title }}</h3>
            <p>{{ $course->description }}</p>
        </div>
    @empty
        <div class="col-span-3 text-center py-12 text-on-surface-variant">
            <span class="material-symbols-outlined text-4xl mb-4">inbox</span>
            <p>No courses found.</p>
        </div>
    @endforelse
</div>
```

## 2. Handling Data Tables

Tables need careful iteration over the `<tbody>` rows. Always include pagination if the collection is paginated.

**Dynamic Blade:**
```blade
<table class="w-full text-left border-collapse">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-outline-variant">
        @forelse($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <button>Edit</button>
                    <!-- Form for deletion -->
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center py-4">No users available.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Pagination Links -->
<div class="mt-4">
    {{ $users->links() }}
</div>
```

## 3. Formatting Data in Views

Always format data nicely for the UI, but do it safely.

- **Dates:** `{{ $user->created_at->format('d M Y') }}` or `{{ $user->created_at->diffForHumans() }}`
- **Numbers:** `{{ number_format($product->price, 2) }}`
- **Strings:** `{{ Str::limit($post->content, 100) }}`

## 4. Conditional Rendering

Sometimes UI elements are only shown based on a state (e.g. a "Published" badge vs a "Draft" badge).

**Static HTML:**
```html
<span class="bg-green-100 text-green-800 px-2 py-1 rounded">Published</span>
```

**Dynamic Blade:**
```blade
<span class="px-2 py-1 rounded {{ $course->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
    {{ ucfirst($course->status) }}
</span>
```
