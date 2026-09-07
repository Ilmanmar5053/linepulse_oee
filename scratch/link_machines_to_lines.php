<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;
use App\Models\ProductionLine;

$lines = ProductionLine::all()->keyBy('code'); // 'FX-1' => id 17, 'FX-11' => id 18, etc.

$machines = Machine::all();
$updated = 0;

foreach ($machines as $m) {
    $matchedLineId = null;
    
    // Check matching by line code:
    // Notice: Check FX-11 and FX-10 first before FX-1
    $order = ['FX-11', 'FX-10', 'FX-1', 'FX-2', 'FX-3', 'FX-4', 'FX-5', 'FX-6', 'FX-7', 'FX-8', 'FX-9'];
    foreach ($order as $lineCode) {
        if (!isset($lines[$lineCode])) continue;
        
        // e.g. MC-FX-11... or MC-MEASURING-FX11 or MC-FX-1...
        $lineNum = str_replace('FX-', '', $lineCode);
        if (
            str_contains($m->code, "MEASURING-FX{$lineNum}") ||
            str_contains($m->code, "MEASURING-{$lineCode}") ||
            str_starts_with($m->code, "MC-FX-{$lineNum}") ||
            str_starts_with($m->code, "MC-{$lineCode}-") ||
            str_contains($m->name, "{$lineCode}")
        ) {
            $matchedLineId = $lines[$lineCode]->id;
            break;
        }
    }
    
    if ($matchedLineId) {
        $m->production_line_id = $matchedLineId;
        $m->save();
        $updated++;
    }
}

echo "Successfully updated {$updated} machines with their production_line_id!\n";

// Let's verify
foreach (ProductionLine::all() as $line) {
    $count = Machine::where('production_line_id', $line->id)->count();
    echo "Line {$line->name} (ID: {$line->id}) has {$count} machines linked.\n";
}
