<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTenantBillingConfigRequest;
use App\Http\Requests\UpdateTenantCommunicationSettingsRequest;
use App\Http\Requests\UpdateTenantPostPaymentSettingsRequest;
use App\Http\Requests\UpdateTenantProfileSettingsRequest;
use App\Models\Tenant;
use App\Services\BillingPolicyResolver;
use App\Services\PostPaymentSettingsResolver;
use App\Services\TenantSettingsService;
use App\Support\TenantSettingsDefaults;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantSettingsController extends Controller
{
    public function __construct(
        protected BillingPolicyResolver $billingPolicyResolver,
        protected TenantSettingsService $tenantSettingsService,
        protected PostPaymentSettingsResolver $postPaymentSettingsResolver,
    ) {
    }

    public function edit(Request $request): View
    {
        $this->authorizeSettings($request);

        $tenant = $request->user()->tenant;
        $billingConfig = $this->billingPolicyResolver->forTenant($tenant);

        return view('settings.edit', [
            'tenant' => $tenant,
            'billingConfig' => $billingConfig,
            'communicationChannels' => $this->tenantSettingsService->get($tenant, 'communication.channels', TenantSettingsDefaults::communicationChannels($tenant)),
            'communicationEvents' => $this->tenantSettingsService->get($tenant, 'communication.events', TenantSettingsDefaults::communicationEvents($tenant)),
            'postPaymentSettings' => $this->postPaymentSettingsResolver->settings($tenant),
            'activeTab' => (string) $request->input('tab', 'profile'),
        ]);
    }

    public function updateProfile(UpdateTenantProfileSettingsRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        $tenant->update($request->validated());
        $this->tenantSettingsService->put($tenant, 'billing.defaults', TenantSettingsDefaults::billingDefaults($tenant));

        return redirect()
            ->route('settings.edit', ['tab' => 'profile'])
            ->with('status', 'Tenant profile settings updated successfully.');
    }

    public function updateBilling(UpdateTenantBillingConfigRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $validated = $request->validated();

        $tenant->update([
            'billing_model' => $validated['billing_model'],
            'currency' => $validated['currency'] ?? $tenant->currency,
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

        $this->tenantSettingsService->put($tenant, 'billing.defaults', TenantSettingsDefaults::billingDefaults($tenant));

        return redirect()
            ->route('settings.edit', ['tab' => 'billing'])
            ->with('status', 'Billing settings updated successfully.');
    }

    public function updateCommunication(UpdateTenantCommunicationSettingsRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $validated = $request->validated();

        $channels = array_merge(
            TenantSettingsDefaults::communicationChannels($tenant),
            $validated['channels'] ?? [],
        );
        $events = array_merge(
            TenantSettingsDefaults::communicationEvents($tenant),
            $validated['events'] ?? [],
        );

        $this->tenantSettingsService->put($tenant, 'communication.channels', $channels);
        $this->tenantSettingsService->put($tenant, 'communication.events', $events);

        return redirect()
            ->route('settings.edit', ['tab' => 'communication'])
            ->with('status', 'Communication settings updated successfully.');
    }

    public function updatePostPayment(UpdateTenantPostPaymentSettingsRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $defaults = TenantSettingsDefaults::paymentPostActions($tenant);
        $validated = $request->validated();

        $settings = [
            'enabled' => (bool) ($validated['enabled'] ?? false),
            'receipts' => array_merge($defaults['receipts'], $validated['receipts'] ?? []),
            'notifications' => array_merge($defaults['notifications'], $validated['notifications'] ?? []),
            'templates' => array_merge($defaults['templates'], $validated['templates'] ?? []),
        ];

        $this->tenantSettingsService->put($tenant, 'payments.post_actions', $settings);

        return redirect()
            ->route('settings.edit', ['tab' => 'post-payment'])
            ->with('status', 'Post-payment action settings updated successfully.');
    }

    protected function authorizeSettings(Request $request): void
    {
        abort_unless(
            $request->user()->isAdmin()
            && $request->user()->can('tenant.settings.manage'),
            403
        );
    }
}
