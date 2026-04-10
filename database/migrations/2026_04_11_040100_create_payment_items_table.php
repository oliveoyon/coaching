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
        Schema::create('payment_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_head_id')->constrained()->restrictOnDelete();
            $table->foreignId('fee_structure_id')->nullable()->constrained()->nullOnDelete();
            $table->string('billing_period_type', 30);
            $table->string('billing_period_key', 50);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->boolean('is_advance')->default(false);
            $table->decimal('charge_amount', 12, 2);
            $table->decimal('due_before', 12, 2);
            $table->decimal('paid_amount', 12, 2);
            $table->decimal('due_after', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'fee_structure_id', 'billing_period_key'], 'payment_items_structure_period_idx');
            $table->index(['tenant_id', 'fee_head_id', 'billing_period_key'], 'payment_items_head_period_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_items');
    }
};
