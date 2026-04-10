<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model): void {
            if (! empty($model->tenant_id)) {
                return;
            }

            $tenantId = app(CurrentTenant::class)->id();

            if ($tenantId !== null) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant($query, int|Tenant $tenant)
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->getKey() : $tenant;

        return $query->where($this->qualifyColumn('tenant_id'), $tenantId);
    }

    public function scopeForCurrentTenant($query)
    {
        $tenantId = app(CurrentTenant::class)->id();

        return $tenantId === null
            ? $query
            : $query->where($this->qualifyColumn('tenant_id'), $tenantId);
    }
}
