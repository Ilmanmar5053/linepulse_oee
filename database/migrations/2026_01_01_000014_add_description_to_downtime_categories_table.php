<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('downtime_categories') && !Schema::hasColumn('downtime_categories', 'description')) {
            Schema::table('downtime_categories', function (Blueprint $table) {
                $table->text('description')->nullable()->after('is_planned');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('downtime_categories') && Schema::hasColumn('downtime_categories', 'description')) {
            Schema::table('downtime_categories', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
