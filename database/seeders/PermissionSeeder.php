<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Permission catalog grouped by module.
     *
     * @var array<string, array<int, array{name: string, label: string}>>
     */
    public const MODULES = [
        'Dashboard' => [
            ['name' => 'dashboard.view', 'label' => 'View dashboard analytics'],
        ],
        'Applicants' => [
            ['name' => 'applicants.view', 'label' => 'View applicant data'],
            ['name' => 'applicants.manage', 'label' => 'Create, edit, and delete applicants'],
            ['name' => 'applicants.review', 'label' => 'Review and decide on applications'],
        ],
        'Documents' => [
            ['name' => 'documents.view', 'label' => 'View submitted documents'],
            ['name' => 'documents.verify', 'label' => 'Verify or reject documents'],
        ],
        'Finance' => [
            ['name' => 'payments.view', 'label' => 'View invoices and payments'],
            ['name' => 'payments.verify', 'label' => 'Verify and settle payments'],
        ],
        'User Management' => [
            ['name' => 'users.view', 'label' => 'View system users'],
            ['name' => 'users.manage', 'label' => 'Add, edit, and remove users'],
            ['name' => 'roles.manage', 'label' => 'Configure roles and permissions'],
        ],
    ];

    /**
     * Default permission assignments per role.
     *
     * @var array<string, array<int, string>|string>
     */
    public const ROLE_PERMISSIONS = [
        // Administrator gets everything.
        'Administrator' => '*',
        'Admission Staff' => [
            'dashboard.view',
            'applicants.view',
            'applicants.manage',
            'applicants.review',
            'documents.view',
            'documents.verify',
        ],
        'Finance' => [
            'dashboard.view',
            'applicants.view',
            'payments.view',
            'payments.verify',
        ],
        'Student' => [],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::MODULES as $permissions) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission['name'], 'web');
            }
        }

        $allPermissions = array_merge(...array_map(
            fn (array $group): array => array_column($group, 'name'),
            array_values(self::MODULES),
        ));

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($permissions === '*') {
                $role->syncPermissions($allPermissions);

                continue;
            }

            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
