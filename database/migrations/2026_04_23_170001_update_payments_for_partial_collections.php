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
        Schema::table('payments', function (Blueprint $table) {
            $table->index('enrollment_id', 'payments_enrollment_id_index');
            $table->dropUnique('payments_enrollment_id_month_unique');
            $table->index(['enrollment_id', 'month'], 'payments_enrollment_month_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_enrollment_month_index');
            $table->dropIndex('payments_enrollment_id_index');
            $table->unique(['enrollment_id', 'month']);
        });
    }
};
