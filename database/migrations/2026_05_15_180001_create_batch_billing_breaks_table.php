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
        Schema::create('batch_billing_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('month', 7);
            $table->string('note', 255)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['batch_id', 'month']);
            $table->index(['batch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_billing_breaks');
    }
};
