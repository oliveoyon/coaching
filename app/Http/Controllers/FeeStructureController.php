<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeStructureRequest;
use App\Http\Requests\UpdateFeeStructureRequest;
use App\Models\Batch;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Program;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeStructureController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FeeStructure::class);

        $structures = FeeStructure::query()
            ->forCurrentTenant()
            ->with(['feeHead'])
            ->latest()
            ->paginate(12);

        return view('fee-structures.index', [
            'structures' => $structures,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', FeeStructure::class);

        return view('fee-structures.create', [
            'feeStructure' => new FeeStructure([
                'applicable_type' => FeeStructure::APPLICABLE_TENANT,
                'billing_model' => $request->user()->tenant->billing_model,
                'is_active' => true,
            ]),
            'feeHeads' => $this->feeHeads(),
            'programs' => $this->programs(),
            'batches' => $this->batches(),
            'billingModels' => $this->billingModels(),
        ]);
    }

    public function store(StoreFeeStructureRequest $request): RedirectResponse
    {
        $feeStructure = FeeStructure::create($this->validatedPayload($request));

        return redirect()
            ->route('fee-structures.edit', $feeStructure)
            ->with('status', 'Fee structure created successfully.');
    }

    public function edit(FeeStructure $feeStructure): View
    {
        $this->authorize('update', $feeStructure);

        return view('fee-structures.edit', [
            'feeStructure' => $feeStructure->load('feeHead'),
            'feeHeads' => $this->feeHeads(),
            'programs' => $this->programs(),
            'batches' => $this->batches(),
            'billingModels' => $this->billingModels(),
        ]);
    }

    public function update(UpdateFeeStructureRequest $request, FeeStructure $feeStructure): RedirectResponse
    {
        $feeStructure->update($this->validatedPayload($request));

        return redirect()
            ->route('fee-structures.edit', $feeStructure)
            ->with('status', 'Fee structure updated successfully.');
    }

    public function destroy(FeeStructure $feeStructure): RedirectResponse
    {
        $this->authorize('delete', $feeStructure);

        $feeStructure->delete();

        return redirect()
            ->route('fee-structures.index')
            ->with('status', 'Fee structure deleted successfully.');
    }

    protected function feeHeads()
    {
        return FeeHead::query()
            ->forCurrentTenant()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    protected function programs()
    {
        return Program::query()
            ->forCurrentTenant()
            ->where('status', Program::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function batches()
    {
        return Batch::query()
            ->forCurrentTenant()
            ->where('status', Batch::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    protected function billingModels(): array
    {
        return [
            Tenant::BILLING_MODEL_PER_STUDENT => 'Per Student',
            Tenant::BILLING_MODEL_PER_COURSE => 'Per Course',
            Tenant::BILLING_MODEL_PER_BATCH => 'Per Batch',
        ];
    }

    protected function validatedPayload(StoreFeeStructureRequest|UpdateFeeStructureRequest $request): array
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        if ($validated['applicable_type'] === FeeStructure::APPLICABLE_TENANT) {
            $validated['applicable_id'] = null;
        }

        return $validated;
    }
}
