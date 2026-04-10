<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class TenantOnboardingService
{
    public function __construct(
        protected AcademicCatalogService $academicCatalogService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function registerTenantAdmin(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            PermissionRegistry::syncPermissions();

            $tenant = Tenant::create([
                'name' => $data['tenant_name'],
                'slug' => $this->generateUniqueSlug($data['tenant_name']),
                'status' => Tenant::STATUS_ACTIVE,
                'billing_model' => Tenant::BILLING_MODEL_PER_STUDENT,
                'contact_person' => $data['name'],
                'phone' => $data['phone'] ?: null,
                'email' => $data['email'],
                'timezone' => 'Asia/Dhaka',
                'currency' => 'BDT',
                'activated_at' => now(),
                'max_branches' => 1,
            ]);

            PermissionRegistry::syncTenantRoles($tenant);
            $this->academicCatalogService->seedDefaults($tenant);

            $this->createDefaultSettings($tenant);

            $user = User::create([
                'tenant_id' => $tenant->getKey(),
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $permissionRegistrar = app(PermissionRegistrar::class);
            $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
            $permissionRegistrar->setPermissionsTeamId($tenant->getKey());
            $user->assignRole(Role::ADMIN);
            $permissionRegistrar->setPermissionsTeamId($originalTeamId);

            return $user;
        });
    }

    protected function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $rootSlug = $baseSlug !== '' ? $baseSlug : 'tenant';
        $slug = $rootSlug;
        $counter = 1;

        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = "{$rootSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function createDefaultSettings(Tenant $tenant): void
    {
        $defaults = [
            'communication.channels' => [
                'sms' => false,
                'whatsapp' => false,
                'email' => true,
            ],
            'communication.events' => [
                'admission' => true,
                'fee_payment' => true,
                'due_reminder' => false,
                'attendance_alert' => false,
                'exam_notice' => false,
                'result_publish' => false,
            ],
            'billing.defaults' => [
                'model' => Tenant::BILLING_MODEL_PER_STUDENT,
                'currency' => 'BDT',
            ],
        ];

        foreach ($defaults as $key => $value) {
            TenantSetting::query()->create([
                'tenant_id' => $tenant->getKey(),
                'key' => $key,
                'value' => $value,
                'autoload' => true,
            ]);
        }
    }
}
