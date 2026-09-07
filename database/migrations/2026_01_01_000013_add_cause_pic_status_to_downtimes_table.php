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
        Schema::table('downtimes', function (Blueprint $table) {
            if (!Schema::hasColumn('downtimes', 'cause')) {
                $table->text('cause')->nullable()->after('description');
            }
            if (!Schema::hasColumn('downtimes', 'pic')) {
                $table->string('pic', 150)->nullable()->after('action_taken');
            }
            if (!Schema::hasColumn('downtimes', 'status')) {
                $table->string('status', 30)->default('CLOSED')->after('pic');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('downtimes', function (Blueprint $table) {
            $table->dropColumn(['cause', 'pic', 'status']);
        });
    }
};
