<?php

use App\Models\Batch;
use App\Models\BatchFee;
use App\Models\FeeType;
use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('batch_fee_id')->nullable()->after('enrollment_id')->constrained('batch_fees')->cascadeOnUpdate()->nullOnDelete();
            $table->string('month', 7)->nullable()->change();
        });

        $tuitionFeeType = FeeType::query()->firstOrCreate(
            ['code' => 'monthly_tuition'],
            [
                'name' => 'Monthly Tuition Fee',
                'frequency' => 'monthly',
                'status' => 'active',
            ],
        );

        Batch::query()->get()->each(function (Batch $batch) use ($tuitionFeeType): void {
            if ((float) $batch->monthly_fee <= 0) {
                return;
            }

            BatchFee::query()->firstOrCreate(
                [
                    'batch_id' => $batch->id,
                    'fee_type_id' => $tuitionFeeType->id,
                ],
                [
                    'amount' => $batch->monthly_fee,
                    'status' => $batch->status === 'active' ? 'active' : 'inactive',
                ],
            );
        });

        Payment::query()->with('enrollment.batch')->get()->each(function (Payment $payment) use ($tuitionFeeType): void {
            $batchId = $payment->enrollment?->batch_id;

            if (! $batchId) {
                return;
            }

            $batchFee = BatchFee::query()->firstOrCreate(
                [
                    'batch_id' => $batchId,
                    'fee_type_id' => $tuitionFeeType->id,
                ],
                [
                    'amount' => $payment->enrollment->batch->monthly_fee,
                    'status' => 'active',
                ],
            );

            $payment->updateQuietly([
                'batch_fee_id' => $batchFee->id,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_fee_id');
            $table->string('month', 7)->nullable(false)->change();
        });
    }
};
