# Form Handling Guide

Forms in static HTML designs do not have the required attributes and security measures to work in Laravel.

## 1. Form Attributes and CSRF

Every form must have a `method` (usually `POST`), an `action` pointing to a named route, and a `@csrf` token if the method is not `GET`.

**Static HTML:**
```html
<form class="space-y-4">
    <input type="text" placeholder="Title">
    <button type="submit">Save</button>
</form>
```

**Dynamic Blade:**
```blade
<form action="{{ route('posts.store') }}" method="POST" class="space-y-4">
    @csrf
    
    <input type="text" name="title" id="title" placeholder="Title" value="{{ old('title') }}">
    <button type="submit">Save</button>
</form>
```

## 2. PUT, PATCH, and DELETE Requests

HTML forms only support `GET` and `POST`. To use `PUT` or `DELETE`, you must use a `POST` form and add the `@method` directive.

```blade
<form action="{{ route('posts.update', $post->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <!-- Form fields -->
</form>

<form action="{{ route('posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
    @csrf
    @method('DELETE')
    
    <button type="submit">Delete</button>
</form>
```

## 3. Validation Errors and Old Input

When form validation fails in the controller, Laravel redirects back with error messages and old input data. You must display these to the user.

- **Old Input:** Use `value="{{ old('field_name', $default) }}"` to keep the user's input after a validation failure.
- **Error Messages:** Use the `@error` directive.
- **Error Styling:** Conditionally apply error styles (like red borders) if a field has an error.

**Dynamic Blade:**
```blade
<div>
    <label for="email">Email</label>
    <input 
        type="email" 
        name="email" 
        id="email" 
        value="{{ old('email', $user->email ?? '') }}"
        class="border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }} rounded px-3 py-2 w-full"
    >
    
    @error('email')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
```

## 4. Disabling JavaScript Submit Blockers

Design exports often include JavaScript that prevents the form from submitting in order to simulate a "loading" state. 
**You must remove `e.preventDefault();`** from any form submit event listeners, otherwise the form will never reach your Laravel backend.
