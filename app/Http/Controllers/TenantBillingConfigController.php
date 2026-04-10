<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTenantBillingConfigRequest;
use App\Models\Tenant;
use App\Services\BillingPolicyResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantBillingConfigController extends Controller
{
    public function __construct(
        protected BillingPolicyResolver $billingPolicyResolver,
    ) {
    }

    public function edit(Request $request): View
    {
        abort_unless(
            $request->user()->isAdmin()
            && $request->user()->can('tenant.settings.manage')
            && $request->user()->can('fees.structure.manage'),
            403
        );

        $tenant = $request->user()->tenant;
        $billingConfig = $this->billingPolicyResolver->forTenant($tenant);

        return view('billing-settings.edit', [
            'tenant' => $tenant,
            'billingConfig' => $billingConfig,
        ]);
    }

    public function update(UpdateTenantBillingConfigRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $validated = $request->validated();

        $tenant->update([
            'billing_model' => $validated['billing_model'],
        ]);

        $tenant->billingConfig()->updateOrCreate(
            ['tenant_id' => $tenant->getKey()],
            [
                'billing_model' => $validated['billing_model'],
                'config' => [
                    'billing_period' => $validated['billing_period'],
                    'unique_student_per_period' => (bool) ($validated['unique_student_per_period'] ?? false),
                    'count_each_batch_separately' => (bool) ($validated['count_each_batch_separately'] ?? false),
                    'count_each_course_separately' => (bool) ($validated['count_each_course_separately'] ?? false),
                    'notes' => $validated['notes'] ?? null,
                ],
            ]
        );

        return redirect()
            ->route('billing-settings.edit')
            ->with('status', 'Tenant billing settings updated successfully.');
    }
}
