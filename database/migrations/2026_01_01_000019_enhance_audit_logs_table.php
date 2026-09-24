<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'user_name')) {
                $table->string('user_name', 150)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('audit_logs', 'user_role')) {
                $table->string('user_role', 100)->nullable()->after('user_name');
            }
            if (!Schema::hasColumn('audit_logs', 'severity')) {
                $table->string('severity', 20)->default('info')->after('module'); // info, warning, danger, critical
            }
            if (!Schema::hasColumn('audit_logs', 'status')) {
                $table->string('status', 20)->default('SUCCESS')->after('severity'); // SUCCESS, FAILED, PENDING
            }
            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable()->after('status');
            }
            if (!Schema::hasColumn('audit_logs', 'url')) {
                $table->string('url', 500)->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('url');
            }
            if (!Schema::hasColumn('audit_logs', 'execution_time_ms')) {
                $table->integer('execution_time_ms')->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('audit_logs', 'hash')) {
                $table->string('hash', 64)->nullable()->after('execution_time_ms');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn([
                'user_name',
                'user_role',
                'severity',
                'status',
                'description',
                'url',
                'user_agent',
                'execution_time_ms',
                'hash'
            ]);
        });
    }
};
