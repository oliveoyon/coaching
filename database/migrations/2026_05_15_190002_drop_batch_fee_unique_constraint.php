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
            $table->dropUnique('batch_fees_batch_id_fee_type_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_fees', function (Blueprint $table) {
            $table->unique(['batch_id', 'fee_type_id']);
        });
    }
};
