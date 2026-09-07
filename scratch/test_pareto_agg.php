<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NgRecord;
use App\Models\ProductionRecord;
use Illuminate\Http\Request;

$ngRecords = NgRecord::with(['items', 'defectReason.defectCategory', 'productionRecord'])->get();

$defectAgg = [];
$sectionAgg = [];
$componentAgg = [
    'ASSY' => 0,
    'ROD' => 0,
    'CAP' => 0,
    'SUPPLEMENTARY' => 0,
];

foreach ($ngRecords as $ng) {
    if ($ng->items && $ng->items->count() > 0) {
        foreach ($ng->items as $item) {
            $qty = (int) $item->quantity;
            if ($qty <= 0) continue;

            $reason = trim($item->reason ?? '');
            if (!$reason) $reason = 'Other / Unspecified Defect';

            $sec = trim($item->section ?? '');
            if (!$sec) $sec = 'General Area';

            $comp = strtoupper(trim($item->component_type ?? 'ASSY'));
            if (isset($componentAgg[$comp])) {
                $componentAgg[$comp] += $qty;
            } else {
                $componentAgg['SUPPLEMENTARY'] += $qty;
            }

            if (!isset($defectAgg[$reason])) {
                $defectAgg[$reason] = [
                    'reason' => $reason,
                    'reject_quantity' => 0,
                    'sections' => [],
                    'component_types' => [],
                ];
            }
            $defectAgg[$reason]['reject_quantity'] += $qty;
            if (!in_array($sec, $defectAgg[$reason]['sections'])) {
                $defectAgg[$reason]['sections'][] = $sec;
            }
            if (!in_array($comp, $defectAgg[$reason]['component_types'])) {
                $defectAgg[$reason]['component_types'][] = $comp;
            }

            if (!isset($sectionAgg[$sec])) {
                $sectionAgg[$sec] = 0;
            }
            $sectionAgg[$sec] += $qty;
        }
    } else {
        // Fallback to legacy fields
        $fields = [
            ['qty' => $ng->ng_assy, 'rsn' => $ng->assy_reason, 'sec' => $ng->assy_section, 'comp' => 'ASSY'],
            ['qty' => $ng->ng_rod, 'rsn' => $ng->rod_reason, 'sec' => $ng->rod_section, 'comp' => 'ROD'],
            ['qty' => $ng->ng_cap, 'rsn' => $ng->cap_reason, 'sec' => $ng->cap_section, 'comp' => 'CAP'],
        ];
        foreach ($fields as $f) {
            $qty = (int) $f['qty'];
            if ($qty <= 0) continue;
            $reason = trim($f['rsn'] ?? '') ?: 'Defect ' . $f['comp'];
            $sec = trim($f['sec'] ?? '') ?: 'Section ' . $f['comp'];
            $comp = $f['comp'];

            $componentAgg[$comp] += $qty;

            if (!isset($defectAgg[$reason])) {
                $defectAgg[$reason] = [
                    'reason' => $reason,
                    'reject_quantity' => 0,
                    'sections' => [],
                    'component_types' => [],
                ];
            }
            $defectAgg[$reason]['reject_quantity'] += $qty;
            if (!in_array($sec, $defectAgg[$reason]['sections'])) {
                $defectAgg[$reason]['sections'][] = $sec;
            }
            if (!in_array($comp, $defectAgg[$reason]['component_types'])) {
                $defectAgg[$reason]['component_types'][] = $comp;
            }

            if (!isset($sectionAgg[$sec])) {
                $sectionAgg[$sec] = 0;
            }
            $sectionAgg[$sec] += $qty;
        }
    }
}

// Sort Defect Aggregation Descending
usort($defectAgg, fn($a, $b) => $b['reject_quantity'] <=> $a['reject_quantity']);
$grandTotal = array_sum(array_column($defectAgg, 'reject_quantity'));
$cumulative = 0;

$topDefects = [];
foreach ($defectAgg as $idx => $row) {
    $qty = $row['reject_quantity'];
    $cumulative += $qty;
    $cumPct = $grandTotal > 0 ? round(($cumulative / $grandTotal) * 100, 1) : 0;
    $pct = $grandTotal > 0 ? round(($qty / $grandTotal) * 100, 1) : 0;

    $topDefects[] = [
        'rank' => $idx + 1,
        'reason' => $row['reason'],
        'reject_quantity' => $qty,
        'percentage' => $pct,
        'cumulative_percentage' => $cumPct,
        'is_vital_few' => ($cumPct <= 80 || ($idx === 0) || ($cumPct - $pct < 80)),
        'section' => implode(', ', $row['sections']) ?: 'General',
        'component_type' => implode(', ', $row['component_types']) ?: 'ASSY',
    ];
}

echo "Grand Total Rejects: {$grandTotal}\n";
echo "Top Defects Count: " . count($topDefects) . "\n";
print_r($topDefects);
