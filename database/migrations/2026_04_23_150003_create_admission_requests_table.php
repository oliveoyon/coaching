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
        Schema::create('admission_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_admission_link_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('name', 150);
            $table->string('phone', 20)->nullable();
            $table->string('guardian_phone', 20);
            $table->string('school')->nullable();
            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
            $table->index(['batch_admission_link_id', 'status']);
            $table->index('guardian_phone');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_requests');
    }
};
