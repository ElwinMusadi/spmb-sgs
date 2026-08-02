# Mockup Data Seeding Guide

When functionalizing a static design, replacing hardcoded HTML with dynamic Blade tags often leaves the page looking empty because the database is empty. 

To ensure the developer can immediately see the UI as the designer intended, you must create Factories and Seeders to populate the database with mockup data.

## 1. Creating Factories

Analyze the static UI design to see what kind of dummy data was used (e.g., specific names, placeholder images, specific statuses). Configure the factory to generate data that matches this aesthetic.

**Command:**
`php artisan make:factory CourseFactory`

**Factory Example:**
```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(3),
            // Use specific statuses from the UI design
            'status' => $this->faker->randomElement(['draft', 'published']), 
            // Use UI.com or unplash for placeholder images if the UI has them
            'thumbnail' => 'https://ui-avatars.com/api/?name='.urlencode($this->faker->word).'&color=7F9CF5&background=EBF4FF',
        ];
    }
}
```

## 2. Creating Database Seeders

Create a seeder that uses the factories to generate a robust set of data.

**Command:**
`php artisan make:seeder CourseSeeder`

**Seeder Example:**
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a test user for the developer to login with
        $instructor = User::factory()->create([
            'name' => 'John Instructor',
            'email' => 'instructor@example.com',
        ]);

        // 2. Generate a sufficient amount of data to fill tables and grids
        Course::factory(25)->create([
            'user_id' => $instructor->id
        ]);
    }
}
```

## 3. Registering the Seeder

Always remember to call your new seeder in the main `DatabaseSeeder.php` so that `php artisan migrate --seed` works flawlessly.

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CourseSeeder::class, // Add your newly created seeder here
        ]);
    }
}
```

## Why this is important:
- **Instant Visual Feedback:** The developer immediately sees if the data binding works and if pagination is functioning (because you seeded 25 items).
- **Edge Cases:** Random faker data can help expose UI issues (like titles being too long and breaking the layout).
