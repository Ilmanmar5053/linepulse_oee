<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Downtime;

echo "=== UPDATING DOWNTIME RECORDS IN DATABASE ===\n";
$dts = Downtime::all();
foreach ($dts as $dt) {
    echo "Processing DT ID: {$dt->id} | Description: {$dt->description} | Action Taken: {$dt->action_taken}\n";
    // If it's a specific trouble, set reason appropriately
    if ($dt->description === 'Tombol Power Rusak') {
        $dt->problem_type = 'Mesin';
        $dt->downtime_reason_id = 4; // Electrical Sensor & PLC Failure
        $dt->save();
    } elseif ($dt->description === 'Tool Patah') {
        $dt->problem_type = 'Dies';
        $dt->downtime_reason_id = 1; // Die & Mold Changeover
        $dt->save();
    } elseif ($dt->description === 'Jig Terlepas') {
        $dt->problem_type = 'Mesin';
        $dt->downtime_reason_id = null;
        $dt->save();
    } elseif ($dt->description === 'Mati Lampu') {
        $dt->problem_type = 'Lain-lain';
        $dt->downtime_reason_id = null;
        $dt->save();
    }
}
echo "Done!\n";
