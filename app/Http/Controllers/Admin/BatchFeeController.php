<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BatchFeeRequest;
use App\Models\Batch;
use App\Models\BatchFee;
use App\Models\FeeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BatchFeeController extends Controller
{
    /**
     * Display the fee setup page for a batch.
     */
    public function index(Batch $batch): View
    {
        $batch->load(['academicClass', 'subject', 'batchFees.feeType']);

        $feeTypes = FeeType::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.batch-fees.index', compact('batch', 'feeTypes'));
    }

    /**
     * Store a newly created batch fee.
     */
    public function store(BatchFeeRequest $request, Batch $batch): RedirectResponse
    {
        $batch->batchFees()->create($request->validated());

        return redirect()
            ->route('admin.batch-fees.index', $batch)
            ->with('success', 'Batch fee configured successfully.');
    }

    /**
     * Update the specified batch fee.
     */
    public function update(BatchFeeRequest $request, Batch $batch, BatchFee $batchFee): RedirectResponse
    {
        abort_unless($batchFee->batch_id === $batch->id, 404);

        $batchFee->update($request->validated());

        return redirect()
            ->route('admin.batch-fees.index', $batch)
            ->with('success', 'Batch fee updated successfully.');
    }
}
