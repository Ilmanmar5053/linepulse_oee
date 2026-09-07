<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DowntimeReason;

echo "=== DOWNTIME REASONS IN DATABASE ===\n";
$reasons = DowntimeReason::all();
foreach ($reasons as $r) {
    echo "ID: {$r->id} | Code: {$r->code} | Name: {$r->name} | Category ID: {$r->downtime_category_id}\n";
}
