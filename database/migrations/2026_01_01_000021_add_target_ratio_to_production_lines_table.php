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
        Schema::table('production_lines', function (Blueprint $table) {
            $table->decimal('target_ratio', 5, 2)->default(83.00)->after('target_oee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_lines', function (Blueprint $table) {
            $table->dropColumn('target_ratio');
        });
    }
};
