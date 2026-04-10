<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Spatie\Permission\PermissionRegistrar;

class PermissionRegistry
{
    public const SYSTEM_TEAM_ID = 0;

    /**
     * @return array<string, array<int, string>>
     */
    public static function grouped(): array
    {
        return [
            'tenant' => [
                'tenant.view',
                'tenant.update',
                'tenant.settings.manage',
            ],
            'teachers' => [
                'teachers.view',
                'teachers.create',
                'teachers.update',
                'teachers.delete',
            ],
            'students' => [
                'students.view',
                'students.create',
                'students.update',
                'students.delete',
            ],
            'batches' => [
                'batches.view',
                'batches.create',
                'batches.update',
                'batches.delete',
            ],
            'enrollments' => [
                'enrollments.view',
                'enrollments.create',
                'enrollments.update',
                'enrollments.delete',
            ],
            'fees' => [
                'fees.view',
                'fees.structure.manage',
                'fees.collect',
                'fees.refund',
            ],
            'payments' => [
                'payments.view',
                'payments.collect',
                'payments.print_receipt',
            ],
            'attendance' => [
                'attendance.view',
                'attendance.mark',
                'attendance.update',
            ],
            'exams' => [
                'exams.view',
                'exams.manage',
                'results.publish',
            ],
            'communications' => [
                'communications.view',
                'communications.send_sms',
                'communications.send_whatsapp',
                'communications.send_email',
            ],
            'reports' => [
                'reports.view',
                'reports.financial',
                'reports.academic',
            ],
            'users' => [
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rolePermissions(): array
    {
        $allPermissions = collect(self::grouped())->flatten()->all();

        return [
            Role::SUPER_ADMIN => $allPermissions,
            Role::ADMIN => $allPermissions,
            Role::TEACHER => [
                'teachers.view',
                'teachers.update',
                'students.view',
                'students.create',
                'students.update',
                'batches.view',
                'batches.create',
                'batches.update',
                'enrollments.view',
                'enrollments.create',
                'enrollments.update',
                'fees.view',
                'payments.view',
                'payments.collect',
                'payments.print_receipt',
                'attendance.view',
                'attendance.mark',
                'attendance.update',
                'exams.view',
                'results.publish',
                'communications.view',
                'communications.send_sms',
                'communications.send_whatsapp',
                'communications.send_email',
                'reports.view',
                'reports.financial',
                'reports.academic',
            ],
            Role::STUDENT => [
                'students.view',
                'attendance.view',
                'exams.view',
            ],
            Role::GUARDIAN => [
                'students.view',
                'payments.view',
                'attendance.view',
                'exams.view',
            ],
        ];
    }

    public static function syncPermissions(string $guardName = 'web'): void
    {
        foreach (self::grouped() as $permissions) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, $guardName);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function syncSuperAdminRole(string $guardName = 'web'): Role
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId(self::SYSTEM_TEAM_ID);

        $role = Role::findOrCreate(Role::SUPER_ADMIN, $guardName);
        $role->syncPermissions(collect(self::rolePermissions()[Role::SUPER_ADMIN]));

        $permissionRegistrar->setPermissionsTeamId($originalTeamId);
        $permissionRegistrar->forgetCachedPermissions();

        return $role;
    }

    public static function syncTenantRoles(Tenant $tenant, string $guardName = 'web'): void
    {
        $tenantRoleNames = [
            Role::ADMIN,
            Role::TEACHER,
            Role::STUDENT,
            Role::GUARDIAN,
        ];

        $permissionRegistrar = app(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId($tenant->getKey());

        foreach ($tenantRoleNames as $roleName) {
            $role = Role::findOrCreate($roleName, $guardName);
            $role->syncPermissions(self::rolePermissions()[$roleName] ?? []);
        }

        $permissionRegistrar->setPermissionsTeamId($originalTeamId);
        $permissionRegistrar->forgetCachedPermissions();
    }
}
