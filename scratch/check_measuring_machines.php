<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;

echo "Total machines in DB: " . Machine::withTrashed()->count() . "\n";
$measuring = Machine::withTrashed()->where('code', 'LIKE', '%MEASUR%')->orWhere('name', 'LIKE', '%Measur%')->get();
echo "Measuring machines found: " . $measuring->count() . "\n";
foreach ($measuring as $m) {
    echo "ID: {$m->id}, Code: {$m->code}, Name: {$m->name}, Line: {$m->line_id}, Active: {$m->is_active}, DeletedAt: {$m->deleted_at}\n";
}

$first10 = Machine::take(10)->get();
echo "\nFirst 10 machines:\n";
foreach ($first10 as $m) {
    echo "ID: {$m->id}, Code: {$m->code}, Name: {$m->name}, Line: {$m->line_id}\n";
}
