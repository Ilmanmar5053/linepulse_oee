<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;

$m = Machine::where('code', 'MC-MEASURING')->first();
if ($m) {
    $m->name = 'Measuring Machine';
    $m->work_center_id = 17;
    $m->is_active = 1;
    $m->deleted_at = null;
    $m->save();
    echo "MC-MEASURING is clean and active!\n";
}
