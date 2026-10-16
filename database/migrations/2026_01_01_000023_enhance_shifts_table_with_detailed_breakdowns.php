<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'code')) {
                $table->string('code', 30)->nullable()->after('id');
            }
            if (!Schema::hasColumn('shifts', 'gross_duration_minutes')) {
                $table->integer('gross_duration_minutes')->default(480)->after('end_time');
            }
            if (!Schema::hasColumn('shifts', 'total_loss_seconds')) {
                $table->integer('total_loss_seconds')->default(4500)->after('break_duration_minutes');
            }
            if (!Schema::hasColumn('shifts', 'planned_production_minutes')) {
                $table->integer('planned_production_minutes')->default(445)->after('total_loss_seconds');
            }
            if (!Schema::hasColumn('shifts', 'work_hours')) {
                $table->decimal('work_hours', 5, 2)->default(7.42)->after('planned_production_minutes');
            }
            if (!Schema::hasColumn('shifts', 'break_breakdown')) {
                $table->json('break_breakdown')->nullable()->after('work_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'gross_duration_minutes',
                'total_loss_seconds',
                'planned_production_minutes',
                'work_hours',
                'break_breakdown',
            ]);
        });
    }
};
