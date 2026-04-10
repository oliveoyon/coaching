<?php

namespace App\Services;

use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Tenant;
use Illuminate\Support\Collection;

class FeeStructureResolver
{
    public function __construct(
        protected BillingPolicyResolver $billingPolicyResolver,
    ) {
    }

    public function forTenant(Tenant $tenant): Collection
    {
        $billingModel = $this->billingPolicyResolver->model($tenant);

        return FeeStructure::query()
            ->with('feeHead')
            ->where('tenant_id', $tenant->getKey())
            ->where('is_active', true)
            ->where(function ($query) use ($billingModel): void {
                $query->whereNull('billing_model')
                    ->orWhere('billing_model', $billingModel);
            })
            ->orderBy('title')
            ->get();
    }

    public function forStudent(Student $student, ?StudentEnrollment $enrollment = null): Collection
    {
        $tenant = $student->tenant;
        $billingModel = $this->billingPolicyResolver->model($tenant);

        return FeeStructure::query()
            ->with('feeHead')
            ->where('tenant_id', $tenant->getKey())
            ->where('is_active', true)
            ->where(function ($query) use ($billingModel): void {
                $query->whereNull('billing_model')
                    ->orWhere('billing_model', $billingModel);
            })
            ->where(function ($query) use ($student, $enrollment): void {
                $query->where('applicable_type', FeeStructure::APPLICABLE_TENANT)
                    ->orWhere(function ($programQuery) use ($student): void {
                        $programQuery
                            ->where('applicable_type', FeeStructure::APPLICABLE_PROGRAM)
                            ->where('applicable_id', $student->enrollments()->whereNotNull('batch_id')->first()?->batch?->program_id);
                    });

                if ($enrollment?->batch_id) {
                    $query->orWhere(function ($batchQuery) use ($enrollment): void {
                        $batchQuery
                            ->where('applicable_type', FeeStructure::APPLICABLE_BATCH)
                            ->where('applicable_id', $enrollment->batch_id);
                    });
                }
            })
            ->orderBy('title')
            ->get();
    }
}
