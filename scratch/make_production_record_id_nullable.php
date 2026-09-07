<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MODIFYING production_record_id TO NULLABLE IN DOWNTIMES TABLE ===\n";

DB::statement("ALTER TABLE downtimes MODIFY production_record_id BIGINT UNSIGNED NULL");

echo "production_record_id is now NULLABLE!\n";
