<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed roles, permissions, and a default super admin user.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage users',
            'manage students',
            'manage batches',
            'manage enrollments',
            'collect payments',
            'approve payments',
            'manage fee setup',
            'settle teacher payments',
            'manage payments',
            'manage expenses',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $teacherRole = Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => 'web']);
        $accountsRole = Role::firstOrCreate(['name' => 'Accounts', 'guard_name' => 'web']);

        $superAdminRole->syncPermissions(Permission::all());
        $adminRole->syncPermissions([
            'manage users',
            'manage students',
            'manage batches',
            'manage enrollments',
            'collect payments',
            'approve payments',
            'manage fee setup',
            'settle teacher payments',
            'manage payments',
            'manage expenses',
            'view reports',
        ]);
        $teacherRole->syncPermissions([
            'collect payments',
            'view reports',
        ]);
        $accountsRole->syncPermissions([
            'collect payments',
            'approve payments',
            'manage fee setup',
            'settle teacher payments',
            'manage payments',
            'manage expenses',
            'view reports',
        ]);

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );

        if (! $superAdmin->hasRole('Super Admin')) {
            $superAdmin->assignRole('Super Admin');
        }
    }
}
