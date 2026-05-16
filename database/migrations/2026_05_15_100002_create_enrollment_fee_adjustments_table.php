<?php

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
        Schema::create('enrollment_fee_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_fee_id')->constrained('batch_fees')->cascadeOnDelete();
            $table->string('adjustment_type', 20)->default('discount');
            $table->string('value_type', 20)->default('fixed');
            $table->decimal('value', 10, 2)->default(0);
            $table->string('effective_from_month', 7)->nullable();
            $table->string('effective_to_month', 7)->nullable();
            $table->text('note')->nullable();
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['enrollment_id', 'batch_fee_id', 'status'], 'enrollment_fee_adjustments_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_fee_adjustments');
    }
};
