<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Downtime;
use App\Models\DowntimeReason;

echo "=== DOWNTIMES TABLE COLUMNS ===\n";
print_r(Schema::getColumnListing('downtimes'));

echo "\n=== DOWNTIME REASONS TABLE COLUMNS ===\n";
print_r(Schema::getColumnListing('downtime_reasons'));

echo "\n=== EXISTING DOWNTIME REASONS DATA ===\n";
$reasons = DowntimeReason::all();
foreach ($reasons as $r) {
    echo "ID: {$r->id} | Code: {$r->code} | Name: {$r->name} | Category: {$r->category}\n";
}
