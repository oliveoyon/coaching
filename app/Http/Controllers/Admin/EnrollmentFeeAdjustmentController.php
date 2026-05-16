<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EnrollmentFeeAdjustmentRequest;
use App\Models\Enrollment;
use App\Models\EnrollmentFeeAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EnrollmentFeeAdjustmentController extends Controller
{
    /**
     * Show fee adjustments for an enrollment.
     */
    public function index(Enrollment $enrollment): View
    {
        $enrollment->load([
            'student',
            'batch.academicClass',
            'batch.subject',
            'batch.batchFees.feeType',
            'feeAdjustments.batchFee.feeType',
            'feeAdjustments.creator',
        ]);

        $batchFees = $enrollment->batch?->batchFees
            ?->where('status', 'active')
            ->sortBy(fn ($batchFee) => $batchFee->feeType?->name)
            ->values() ?? collect();

        return view('admin.enrollment-fee-adjustments.index', compact('enrollment', 'batchFees'));
    }

    /**
     * Store a newly created fee adjustment.
     */
    public function store(EnrollmentFeeAdjustmentRequest $request, Enrollment $enrollment): RedirectResponse
    {
        $enrollment->feeAdjustments()->create($request->validated() + [
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.enrollment-fee-adjustments.index', $enrollment)
            ->with('success', 'Fee adjustment added successfully.');
    }

    /**
     * Update the specified fee adjustment.
     */
    public function update(EnrollmentFeeAdjustmentRequest $request, Enrollment $enrollment, EnrollmentFeeAdjustment $feeAdjustment): RedirectResponse
    {
        abort_unless($feeAdjustment->enrollment_id === $enrollment->id, 404);

        $feeAdjustment->update($request->validated());

        return redirect()
            ->route('admin.enrollment-fee-adjustments.index', $enrollment)
            ->with('success', 'Fee adjustment updated successfully.');
    }
}
