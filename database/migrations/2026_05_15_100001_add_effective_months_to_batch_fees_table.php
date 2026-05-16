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
        Schema::table('batch_fees', function (Blueprint $table) {
            $table->string('effective_from_month', 7)->nullable()->after('amount');
            $table->string('effective_to_month', 7)->nullable()->after('effective_from_month');
            $table->index(['effective_from_month', 'effective_to_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_fees', function (Blueprint $table) {
            $table->dropIndex(['effective_from_month', 'effective_to_month']);
            $table->dropColumn(['effective_from_month', 'effective_to_month']);
        });
    }
};
