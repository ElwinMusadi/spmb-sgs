<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * The default roles for the admission system.
     *
     * @var array<int, string>
     */
    public const ROLES = [
        'Administrator',
        'Student',
        'Finance',
        'Admission Staff',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@stellagracia.com'],
            [
                'name' => 'Winda Ndolu',
                'password' => Hash::make('Admin1234'),
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['Administrator']);

        $student = User::where('email', 'elwinmusadi@gmail.com')->first();

        if ($student && ! $student->hasRole('Student')) {
            $student->assignRole('Student');
        }
    }
}
