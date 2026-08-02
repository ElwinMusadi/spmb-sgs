# Backend Architecture Guide

When functionalizing a static design, you must build the underlying backend infrastructure. This involves creating Migrations, Models, Controllers, and Routes.

## 1. Migrations & Models

Look at the UI design and deduce what fields are needed. Use Artisan commands to generate the model and migration simultaneously.

**Command:**
`php artisan make:model Course -m`

**Migration Example:**
```php
public function up(): void
{
    Schema::create('courses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('title');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->string('status')->default('draft'); // draft, published
        $table->timestamps();
    });
}
```

**Model Example:**
Always specify `$fillable` or `$guarded` to prevent Mass Assignment vulnerabilities, and define the relationships needed by the UI.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'user_id'
    ];

    // Important for Data Binding in Views
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

## 2. Routes (`routes/web.php` or `routes/api.php`)

Group routes logically. Apply middleware (like `auth`) to protect pages that require login (like dashboards).

```php
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Resourceful routing is often best for CRUD operations
    Route::resource('courses', CourseController::class);
});

// Public Routes
Route::get('/courses', [CourseController::class, 'publicIndex'])->name('courses.public');
```

## 3. Controllers

Controllers are the bridge between your Models and the Blade Views. 

### Data Fetching (GET)
Use eager loading (`with()`) to prevent N+1 query problems when fetching collections.

```php
namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        // Eager load the 'instructor' relationship to avoid N+1 in the view
        $courses = Course::with('instructor')
                         ->where('user_id', auth()->id())
                         ->latest()
                         ->paginate(10);
                         
        return view('courses.index', compact('courses'));
    }
}
```

### Data Processing (POST/PUT/DELETE)
Always validate the incoming request. Use the validated data to create or update records.

```php
public function store(Request $request)
{
    // 1. Validate
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:draft,published'
    ]);

    // 2. Process (Inject relationships if needed)
    $validated['user_id'] = auth()->id();
    $validated['slug'] = str()->slug($validated['title']);

    // 3. Save
    Course::create($validated);

    // 4. Redirect with feedback
    return redirect()->route('courses.index')
                     ->with('success', 'Course created successfully!');
}
```
