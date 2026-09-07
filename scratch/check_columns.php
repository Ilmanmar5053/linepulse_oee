<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== MACHINES TABLE COLUMNS ===\n";
print_r(Schema::getColumnListing('machines'));

echo "\n=== PRODUCTION LINES TABLE COLUMNS ===\n";
print_r(Schema::getColumnListing('production_lines'));
