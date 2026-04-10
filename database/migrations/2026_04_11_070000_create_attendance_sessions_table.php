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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('taken_by')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'batch_id', 'attendance_date']);
            $table->index(['tenant_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
