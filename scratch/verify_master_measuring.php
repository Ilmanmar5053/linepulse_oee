<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;

$machines = Machine::where('code', 'MC-MEASURING')->get();
echo "Active MC-MEASURING count in Master Data: " . $machines->count() . "\n";
foreach ($machines as $m) {
    echo "ID: {$m->id}, Code: {$m->code}, Name: {$m->name}, Status: {$m->status->value}\n";
}
