<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;
use App\Models\WorkCenter;
use App\Models\ProductionLine;
use Illuminate\Support\Facades\DB;

$wc17 = WorkCenter::find(17);
print_r($wc17 ? $wc17->toArray() : 'not found');

$prodRecs = DB::table('production_records')->where('machine_id', 411)->get();
echo "Production Records with MC-MEASURING:\n";
print_r($prodRecs->toArray());

// If production records are in line_id 17 (FX-1), link work_center_id of MC-MEASURING to work_center of line 17
if ($prodRecs->count() > 0) {
    $lineId = $prodRecs->first()->line_id;
    echo "Line ID from records: $lineId\n";
    $targetWc = WorkCenter::where('production_line_id', $lineId)->first() ?? WorkCenter::where('line_id', $lineId)->first() ?? WorkCenter::find(17);
    if ($targetWc) {
        DB::table('machines')->where('id', 411)->update(['work_center_id' => $targetWc->id]);
        echo "Updated MC-MEASURING work_center_id to {$targetWc->id}\n";
    }
}
