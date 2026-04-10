<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTenantBillingConfigRequest;
use App\Services\BillingPolicyResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantBillingConfigController extends Controller
{
    public function __construct(
        protected BillingPolicyResolver $billingPolicyResolver,
    ) {
    }

    public function edit(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()->isAdmin()
            && $request->user()->can('tenant.settings.manage')
            && $request->user()->can('fees.structure.manage'),
            403
        );

        return redirect()->route('settings.edit', ['tab' => 'billing']);
    }

    public function update(UpdateTenantBillingConfigRequest $request): RedirectResponse
    {
        return redirect()->route('settings.edit', ['tab' => 'billing']);
    }
}
