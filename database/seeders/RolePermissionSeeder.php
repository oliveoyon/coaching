<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        PermissionRegistry::syncPermissions();
        PermissionRegistry::syncSuperAdminRole();

        Tenant::query()->each(function (Tenant $tenant): void {
            PermissionRegistry::syncTenantRoles($tenant);
        });
    }
}
