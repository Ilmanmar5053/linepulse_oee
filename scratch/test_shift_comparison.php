<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OeeRecord;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

echo "=== REALTIME SHIFT COMPARISON TEST ===\n";
$shifts = Shift::all();
foreach ($shifts as $s) {
    $records = OeeRecord::where('shift_id', $s->id)->get();
    $count = $records->count();

    if ($count > 0) {
        $avgAvail = round($records->avg('availability'), 2);
        $avgPerf = round($records->avg('performance'), 2);
        $avgQual = round($records->avg('quality'), 2);
        $avgOee = round(($avgAvail/100) * ($avgPerf/100) * ($avgQual/100) * 100, 2);

        $target = $records->sum('target_quantity');
        $totalActual = $records->sum('total_quantity');
        $good = $records->sum('good_quantity');
        $reject = $records->sum('reject_quantity');

        echo sprintf(
            "Shift %d (%s): Records=%d | OEE=%.2f%% | Avail=%.2f%% | Perf=%.2f%% | Qual=%.2f%% | Target=%d | TotalMeasuring=%d | FinishGood=%d | NG=%d\n",
            $s->id,
            $s->name,
            $count,
            $avgOee,
            $avgAvail,
            $avgPerf,
            $avgQual,
            $target,
            $totalActual,
            $good,
            $reject
        );
    } else {
        echo "Shift {$s->id} ({$s->name}): No records found.\n";
    }
}
