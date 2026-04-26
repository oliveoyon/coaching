<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\Teacher;
use App\Models\TeacherSettlement;
use Illuminate\Support\Facades\DB;

class TeacherSettlementService
{
    /**
     * Create a teacher settlement and allocate it against oldest outstanding distributions.
     */
    public function settle(Teacher $teacher, float $amount, string $settlementDate, int $paidBy, ?string $note = null): TeacherSettlement
    {
        return DB::transaction(function () use ($teacher, $amount, $settlementDate, $paidBy, $note): TeacherSettlement {
            $remainingCents = (int) round($amount * 100);

            $settlement = TeacherSettlement::create([
                'teacher_id' => $teacher->id,
                'amount' => number_format($amount, 2, '.', ''),
                'settlement_date' => $settlementDate,
                'paid_by' => $paidBy,
                'note' => $note,
            ]);

            $distributions = Distribution::query()
                ->withSum('settlementItems', 'amount')
                ->with(['payment.enrollment.batch', 'payment.collector'])
                ->where('teacher_id', $teacher->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($distributions as $distribution) {
                if ($remainingCents <= 0) {
                    break;
                }

                $settledCents = (int) round(((float) ($distribution->settlement_items_sum_amount ?? 0)) * 100);
                $distributionCents = (int) round(((float) $distribution->amount) * 100);
                $outstandingCents = max(0, $distributionCents - $settledCents);

                if ($outstandingCents === 0) {
                    continue;
                }

                $applyCents = min($remainingCents, $outstandingCents);

                $settlement->items()->create([
                    'distribution_id' => $distribution->id,
                    'amount' => number_format($applyCents / 100, 2, '.', ''),
                ]);

                $remainingCents -= $applyCents;
            }

            return $settlement->load(['teacher.user', 'payer', 'items.distribution.payment']);
        });
    }
}
