<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class IncomeDistributionService
{
    /**
     * Create teacher income distribution rows for an approved payment.
     */
    public function distribute(Payment $payment): void
    {
        $payment->loadMissing([
            'distributions',
            'enrollment.batch.teachers',
        ]);

        if ($payment->status !== 'approved' || $payment->distributions->isNotEmpty()) {
            return;
        }

        $batch = $payment->enrollment?->batch;
        $teachers = $batch?->teachers?->where('status', 'active')->values();

        if (! $batch || ! $teachers || $teachers->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($payment, $batch, $teachers): void {
            if ($payment->distributions()->exists()) {
                return;
            }

            if ($batch->distribution_type === 'single') {
                $payment->distributions()->create([
                    'teacher_id' => $teachers->first()->id,
                    'amount' => $payment->amount,
                ]);

                return;
            }

            $teacherCount = $teachers->count();
            $totalCents = (int) round(((float) $payment->amount) * 100);
            $baseShare = intdiv($totalCents, $teacherCount);
            $remainder = $totalCents % $teacherCount;

            foreach ($teachers as $index => $teacher) {
                $shareCents = $baseShare + ($index === 0 ? $remainder : 0);

                $payment->distributions()->create([
                    'teacher_id' => $teacher->id,
                    'amount' => number_format($shareCents / 100, 2, '.', ''),
                ]);
            }
        });
    }
}
