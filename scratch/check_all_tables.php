<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== ALL TABLES IN DB ===\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $vals = array_values((array)$t);
    $tableName = $vals[0];
    $count = DB::table($tableName)->count();
    echo str_pad($tableName, 30) . ": $count rows\n";
}
