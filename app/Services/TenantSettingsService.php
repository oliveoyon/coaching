<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantSetting;

class TenantSettingsService
{
    /**
     * @param  mixed  $default
     * @return mixed
     */
    public function get(Tenant $tenant, string $key, $default = null)
    {
        $setting = TenantSetting::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('key', $key)
            ->first();

        return $setting?->value ?? $default;
    }

    /**
     * @param  array<string, mixed>  $value
     */
    public function put(Tenant $tenant, string $key, array $value, bool $autoload = true): TenantSetting
    {
        return TenantSetting::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->getKey(),
                'key' => $key,
            ],
            [
                'value' => $value,
                'autoload' => $autoload,
            ],
        );
    }
}
