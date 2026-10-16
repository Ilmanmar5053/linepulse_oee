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
            if (!Schema::hasColumn('downtimes', 'follow_up_action')) {
                $table->text('follow_up_action')->nullable()->after('action_taken');
            }
            if (!Schema::hasColumn('downtimes', 'due_date')) {
                $table->date('due_date')->nullable()->after('follow_up_action');
            }
            if (!Schema::hasColumn('downtimes', 'pic_followup')) {
                $table->string('pic_followup', 150)->nullable()->after('due_date');
            }
        });

        Schema::table('ng_records', function (Blueprint $table) {
            if (!Schema::hasColumn('ng_records', 'follow_up_action')) {
                $table->text('follow_up_action')->nullable()->after('action_taken');
            }
            if (!Schema::hasColumn('ng_records', 'due_date')) {
                $table->date('due_date')->nullable()->after('follow_up_action');
            }
            if (!Schema::hasColumn('ng_records', 'pic_followup')) {
                $table->string('pic_followup', 150)->nullable()->after('due_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('downtimes', function (Blueprint $table) {
            $table->dropColumn(['follow_up_action', 'due_date', 'pic_followup']);
        });

        Schema::table('ng_records', function (Blueprint $table) {
            $table->dropColumn(['follow_up_action', 'due_date', 'pic_followup']);
        });
    }
};
