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
        Schema::table('batch_schedules', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->after('batch_id')->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->after('subject_id')->constrained('teachers')->nullOnDelete();
            $table->string('session_type', 20)->default('regular')->after('end_time');
            $table->boolean('is_extra')->default(false)->after('session_type');
            $table->text('notes')->nullable()->after('sort_order');

            $table->index(['tenant_id', 'day_of_week']);
            $table->index(['tenant_id', 'teacher_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_schedules', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'day_of_week']);
            $table->dropIndex(['tenant_id', 'teacher_id', 'day_of_week']);
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropConstrainedForeignId('subject_id');
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn(['session_type', 'is_extra', 'notes']);
        });
    }
};
