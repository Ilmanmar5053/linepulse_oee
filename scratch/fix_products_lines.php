<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductionLine;

$lineMap = [];
foreach (ProductionLine::all() as $l) {
    $code = strtoupper(trim($l->code));
    $name = strtoupper(trim($l->name));
    $lineMap[$code] = $l->id;
    $lineMap[$name] = $l->id;
}

echo "Line Map:\n";
print_r($lineMap);

$fx1Id = $lineMap['FX-1'] ?? null;
$fx2Id = $lineMap['FX-2'] ?? null;
$fx3Id = $lineMap['FX-3'] ?? null;
$fx4Id = $lineMap['FX-4'] ?? null;
$fx5Id = $lineMap['FX-5'] ?? null;
$fx6Id = $lineMap['FX-6'] ?? null;
$fx7Id = $lineMap['FX-7'] ?? null;
$fx8Id = $lineMap['FX-8'] ?? null;
$fx9Id = $lineMap['FX-9'] ?? null;
$fx10Id = $lineMap['FX-10'] ?? null;
$fx11Id = $lineMap['FX-11'] ?? null;

$mapping = [
    // FX-1
    'RT140-SKC' => $fx1Id,
    '12R' => $fx1Id,
    '1GZ' => $fx1Id,
    '22R' => $fx1Id,
    '3RZ' => $fx1Id,
    '3SFE' => $fx1Id,
    '3SG' => $fx1Id,
    '3VZ' => $fx1Id,
    '5K' => $fx1Id,
    '5S FE' => $fx1Id,

    // FX-2
    'E9' => $fx2Id,
    'EA8' => $fx2Id,
    'RT100/120' => $fx2Id,
    'E7/8' => $fx2Id,
    '4D5-INA' => $fx2Id,
    'ED-ADM' => $fx2Id,
    'ED-YC' => $fx2Id,
    'EF-ADM' => $fx2Id,
    'EF-YC' => $fx2Id,
    'HR12' => $fx2Id,

    // FX-3
    'RT80/90' => $fx3Id,
    'SL-L' => $fx3Id,
    'SL-S' => $fx3Id,
    'KR-YC' => $fx3Id,
    '2NZ' => $fx3Id,
    '2NZ-CKD' => $fx3Id,
    'FX3-1NR-ADM' => $fx3Id,
    'FX3-1NR-TMMIN' => $fx3Id,
    'FX3-2NR-ADM' => $fx3Id,
    'FX3-2NR-TMMIN' => $fx3Id,

    // FX-4
    'KR-ADM' => $fx4Id,
    '1TR' => $fx4Id,
    '2TR' => $fx4Id,
    '2TR-EXP' => $fx4Id,
    '4N14' => $fx4Id,

    // FX-5
    '1ND' => $fx5Id,
    '1NZ' => $fx5Id,
    '4N13' => $fx5Id,
    '4N14-4X45' => $fx5Id,
    '604F-KR' => $fx5Id,

    // FX-6
    '3SZ-HL' => $fx6Id,
    '3SZ' => $fx6Id,
    'K3-HL' => $fx6Id,
    'K3' => $fx6Id,

    // FX-7
    'FX7-1NR-ADM' => $fx7Id,
    'FX7-1NR-TMMIN' => $fx7Id,
    'FX7-2NR-ADM' => $fx7Id,
    'FX7-2NR-TMMIN' => $fx7Id,

    // FX-8
    '8AR' => $fx8Id,
    '2UR-FSE' => $fx8Id,
    '2UR-GSE' => $fx8Id,
    '3UR' => $fx8Id,
    '4GR' => $fx8Id,

    // FX-9
    'FX9-1NR-ADM' => $fx9Id,
    'FX9-1NR-TMMIN' => $fx9Id,
    'FX9-2NR-ADM' => $fx9Id,
    'FX9-2NR-TMMIN' => $fx9Id,
    'D81F' => $fx9Id,

    // FX-10 (OP10)
    'OP10-1NR-ADM' => $fx10Id,
    'OP10-1NR-TMMIN' => $fx10Id,
    'OP10-2NR-ADM' => $fx10Id,
    'OP10-2NR-TMMIN' => $fx10Id,
    'HR12 (5.5s)' => $fx10Id,

    // FX-11
    'FX11-1NR-ADM' => $fx11Id,
    'FX11-1NR-TMMIN' => $fx11Id,
    'FX11-2NR-ADM' => $fx11Id,
    'FX11-2NR-TMMIN' => $fx11Id,
    'HR12 (97.5s)' => $fx11Id,
];

foreach (Product::all() as $p) {
    if (isset($mapping[$p->name])) {
        $targetLineId = $mapping[$p->name];
        $p->production_line_id = $targetLineId;
        $p->save();
        echo "Updated Product: {$p->name} -> Line ID: {$targetLineId}\n";
    } else {
        // Match by SKU prefix
        if (str_contains($p->sku, 'OP10') || str_contains($p->name, 'OP10') || $p->sku === '1210A4LC0A 5') {
            $p->production_line_id = $fx10Id;
            $p->save();
            echo "Updated OP10 Product: {$p->name} -> Line ID: {$fx10Id}\n";
        } elseif (str_contains($p->sku, 'FX11') || str_contains($p->name, 'FX11') || $p->sku === '1210A4LC0A 97') {
            $p->production_line_id = $fx11Id;
            $p->save();
            echo "Updated FX11 Product: {$p->name} -> Line ID: {$fx11Id}\n";
        }
    }
}

echo "\nVerification of Line FX-10 Products:\n";
$fx10Products = Product::where('production_line_id', $fx10Id)->get();
foreach ($fx10Products as $p) {
    echo "- FX-10 Product: {$p->name} (SKU: {$p->sku}, Ideal Cycle: {$p->ideal_cycle_time}s)\n";
}

echo "\nVerification of Line FX-11 Products:\n";
$fx11Products = Product::where('production_line_id', $fx11Id)->get();
foreach ($fx11Products as $p) {
    echo "- FX-11 Product: {$p->name} (SKU: {$p->sku}, Ideal Cycle: {$p->ideal_cycle_time}s)\n";
}
