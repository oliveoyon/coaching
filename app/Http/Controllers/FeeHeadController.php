<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeHeadRequest;
use App\Http\Requests\UpdateFeeHeadRequest;
use App\Models\FeeHead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeHeadController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FeeHead::class);

        $feeHeads = FeeHead::query()
            ->forCurrentTenant()
            ->withCount('structures')
            ->latest()
            ->paginate(12);

        return view('fee-heads.index', [
            'feeHeads' => $feeHeads,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', FeeHead::class);

        return view('fee-heads.create', [
            'feeHead' => new FeeHead([
                'type' => FeeHead::TYPE_MONTHLY_TUITION,
                'frequency' => FeeHead::FREQUENCY_MONTHLY,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(StoreFeeHeadRequest $request): RedirectResponse
    {
        $feeHead = FeeHead::create(array_merge($request->validated(), [
            'tenant_id' => $request->user()->tenant_id,
            'is_active' => (bool) ($request->validated()['is_active'] ?? false),
        ]));

        return redirect()
            ->route('fee-heads.edit', $feeHead)
            ->with('status', 'Fee head created successfully.');
    }

    public function edit(FeeHead $feeHead): View
    {
        $this->authorize('update', $feeHead);

        return view('fee-heads.edit', [
            'feeHead' => $feeHead,
        ]);
    }

    public function update(UpdateFeeHeadRequest $request, FeeHead $feeHead): RedirectResponse
    {
        $feeHead->update(array_merge($request->validated(), [
            'is_active' => (bool) ($request->validated()['is_active'] ?? false),
        ]));

        return redirect()
            ->route('fee-heads.edit', $feeHead)
            ->with('status', 'Fee head updated successfully.');
    }

    public function destroy(FeeHead $feeHead): RedirectResponse
    {
        $this->authorize('delete', $feeHead);

        $feeHead->delete();

        return redirect()
            ->route('fee-heads.index')
            ->with('status', 'Fee head deleted successfully.');
    }
}
