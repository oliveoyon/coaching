<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantBillingConfig;

class BillingPolicyResolver
{
    public function forTenant(Tenant $tenant): TenantBillingConfig
    {
        return $tenant->billingConfig ?: new TenantBillingConfig([
            'tenant_id' => $tenant->getKey(),
            'billing_model' => $tenant->billing_model,
            'config' => $this->defaultConfig($tenant->billing_model),
        ]);
    }

    public function model(Tenant $tenant): string
    {
        return $this->forTenant($tenant)->billing_model;
    }

    /**
     * @return array<string, mixed>
     */
    public function options(Tenant $tenant): array
    {
        return $this->forTenant($tenant)->config ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultConfig(string $billingModel): array
    {
        return match ($billingModel) {
            Tenant::BILLING_MODEL_PER_STUDENT => [
                'billing_period' => 'monthly',
                'unique_student_per_period' => true,
            ],
            Tenant::BILLING_MODEL_PER_BATCH => [
                'billing_period' => 'monthly',
                'count_each_batch_separately' => true,
            ],
            Tenant::BILLING_MODEL_PER_COURSE => [
                'billing_period' => 'monthly',
                'count_each_course_separately' => true,
            ],
            default => [
                'billing_period' => 'monthly',
            ],
        };
    }
}
