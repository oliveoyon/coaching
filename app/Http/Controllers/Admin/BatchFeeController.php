<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BatchFeeRequest;
use App\Models\Batch;
use App\Models\BatchBillingBreak;
use App\Models\BatchFee;
use App\Models\BatchFeeMonthOverride;
use App\Models\FeeType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatchFeeController extends Controller
{
    /**
     * Display all batches for fee setup.
     */
    public function directory(): View
    {
        $batches = Batch::query()
            ->with(['academicClass', 'subject', 'teachers.user', 'batchFees.feeType', 'billingBreaks'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.batch-fees.directory', compact('batches'));
    }

    /**
     * Display the fee setup page for a batch.
     */
    public function index(Batch $batch): View
    {
        $batch->load(['academicClass', 'subject', 'batchFees.feeType', 'batchFees.monthOverrides', 'billingBreaks']);

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
        $validated = $request->validated();
        unset($validated['apply_from_month']);

        $batch->batchFees()->create($validated);

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

        $validated = $request->validated();
        $applyFromMonth = $validated['apply_from_month'] ?? null;
        unset($validated['apply_from_month']);

        if ($applyFromMonth && $applyFromMonth !== $batchFee->effective_from_month) {
            DB::transaction(function () use ($batchFee, $validated, $applyFromMonth): void {
                $newStart = Carbon::createFromFormat('Y-m', $applyFromMonth)->startOfMonth();
                $previousEnd = $newStart->copy()->subMonth()->format('Y-m');

                $batchFee->update([
                    'effective_to_month' => $previousEnd,
                ]);

                BatchFee::create([
                    'batch_id' => $batchFee->batch_id,
                    'fee_type_id' => $batchFee->fee_type_id,
                    'amount' => $validated['amount'],
                    'effective_from_month' => $applyFromMonth,
                    'effective_to_month' => $validated['effective_to_month'] ?? null,
                    'status' => $validated['status'],
                ]);
            });
        } else {
            $batchFee->update($validated);
        }

        return redirect()
            ->route('admin.batch-fees.index', $batch)
            ->with('success', 'Batch fee updated successfully.');
    }

    /**
     * Store a paused billing month for the batch.
     */
    public function storeBillingBreak(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $batch->billingBreaks()->updateOrCreate(
            ['month' => $validated['month']],
            [
                'note' => $validated['note'] ?? null,
                'status' => 'active',
            ]
        );

        return redirect()
            ->route('admin.batch-fees.index', $batch)
            ->with('success', 'Paused month added successfully.');
    }

    /**
     * Remove a paused billing month from the batch.
     */
    public function destroyBillingBreak(Batch $batch, BatchBillingBreak $billingBreak): RedirectResponse
    {
        abort_unless($billingBreak->batch_id === $batch->id, 404);

        $billingBreak->delete();

        return redirect()
            ->route('admin.batch-fees.index', $batch)
            ->with('success', 'Paused month removed successfully.');
    }

    /**
     * Store a month-specific amount for a fee item.
     */
    public function storeMonthOverride(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'batch_fee_id' => ['required', 'exists:batch_fees,id'],
            'month' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $batchFee = BatchFee::query()->findOrFail((int) $validated['batch_fee_id']);
        abort_unless($batchFee->batch_id === $batch->id, 404);

        $batchFee->monthOverrides()->updateOrCreate(
            ['month' => $validated['month']],
            [
                'amount' => number_format((float) $validated['amount'], 2, '.', ''),
                'note' => $validated['note'] ?? null,
                'status' => 'active',
            ]
        );

        return redirect()
            ->route('admin.batch-fees.index', $batch)
            ->with('success', 'Special month amount saved successfully.');
    }

    /**
     * Remove a month-specific amount from a fee item.
     */
    public function destroyMonthOverride(Batch $batch, BatchFeeMonthOverride $monthOverride): RedirectResponse
    {
        abort_unless($monthOverride->batchFee?->batch_id === $batch->id, 404);

        $monthOverride->delete();

        return redirect()
            ->route('admin.batch-fees.index', $batch)
            ->with('success', 'Special month amount removed successfully.');
    }
}
