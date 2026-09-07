<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== TRUNCATING ALL TRANSACTIONAL HISTORY TABLES ===\n";

Schema::disableForeignKeyConstraints();

$historyTables = [
    'production_records',
    'production_orders',
    'production_plans',
    'daily_production_summaries',
    'shift_summaries',
    'oee_records',
    'downtimes',
    'machine_events',
    'machine_status_logs',
    'quality_records',
    'audit_logs',
];

foreach ($historyTables as $table) {
    if (Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "Table '$table' truncated successfully.\n";
    }
}

// Reset machines live telemetry status
DB::table('machines')->update([
    'status' => 'IDLE',
]);

Schema::enableForeignKeyConstraints();

echo "\nAll transaction & historical records successfully cleared!\n";
