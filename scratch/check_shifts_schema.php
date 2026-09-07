<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Shift;
use Illuminate\Support\Facades\Schema;

echo "Shifts Table Columns:\n";
$cols = Schema::getColumnListing('shifts');
print_r($cols);

echo "\nCurrent Shifts Data:\n";
$shifts = Shift::all();
foreach ($shifts as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | Code: {$s->code} | Start: {$s->start_time} | End: {$s->end_time}\n";
}
