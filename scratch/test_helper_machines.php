<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;
use App\Models\ProductionLine;

$allMachines = Machine::with('workCenter.productionLine')->get();
$allLines = ProductionLine::all();

foreach ($allLines as $line) {
    $lineId = $line->id;
    $lineCode = $line->code;
    $lineNum = str_replace('FX-', '', $lineCode);
    
    $filtered = $allMachines->filter(function($m) use ($lineId, $lineNum) {
        $mLineId = $m->workCenter ? $m->workCenter->production_line_id : null;
        if ($mLineId && $mLineId == $lineId) return true;
        if ($lineNum && (
            (str_starts_with($m->code, "MC-FX-{$lineNum}")) || 
            (str_contains($m->code, "MEASURING-FX{$lineNum}")) ||
            (str_contains($m->name, "FX-{$lineNum}"))
        )) {
            return true;
        }
        return false;
    });
    
    echo "Line {$line->name} (ID: {$line->id}) -> Found " . $filtered->count() . " machines. First machine ID: " . ($filtered->first() ? $filtered->first()->id : 'NONE') . " ({$filtered->first()?->name} - {$filtered->first()?->code})\n";
}
