<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\MasterDataController;
use Illuminate\Http\Request;

$controller = app(MasterDataController::class);

echo "=== TEST 3-COLUMN EXCEL IMPORT: BAGIAN NG ===\n";
$excelNgRows = [
    [
        "Kode Bagian NG" => "SEC-TEST-3COL-1",
        "Nama Bagian NG" => "Small End (Pin Bore)",
        "Status Aktif (1=Aktif, 0=Nonaktif)" => 1
    ],
    [
        "Kode Bagian NG" => "SEC-TEST-3COL-2",
        "Nama Bagian NG" => "Big End (Crank Bore)",
        "Status Aktif (1=Aktif, 0=Nonaktif)" => 1
    ]
];

$reqNg = new Request(['rows' => $excelNgRows]);
$resNg = $controller->importNgSections($reqNg);
$dataNg = $resNg->getData();
echo "NG Import Success: " . ($dataNg->success ? 'YES' : 'NO') . " | Msg: " . $dataNg->message . "\n";
echo "Imported: {$dataNg->imported_count}, Updated: {$dataNg->updated_count}, Errors: " . count($dataNg->errors) . "\n";

echo "\n=== TEST 2-COLUMN EXCEL IMPORT: PENYEBAB / REMARK ===\n";
$excelDefRows = [
    [
        "Kategori Defect" => "Dimensi & Geometri",
        "Nama Penyebab / Remark" => "Diameter Pin Out of Spec (2-Col Test)"
    ],
    [
        "Kategori Defect" => "Kualitas Permukaan & Visual",
        "Nama Penyebab / Remark" => "Surface Scratch & Dent (2-Col Test)"
    ]
];

$reqDef = new Request(['rows' => $excelDefRows]);
$resDef = $controller->importDefectReasons($reqDef);
$dataDef = $resDef->getData();
echo "Defect Import Success: " . ($dataDef->success ? 'YES' : 'NO') . " | Msg: " . $dataDef->message . "\n";
echo "Imported: {$dataDef->imported_count}, Updated: {$dataDef->updated_count}, Errors: " . count($dataDef->errors) . "\n";

// Cleanup test items
\App\Models\NgSection::where('code', 'LIKE', 'SEC-TEST-3COL-%')->delete();
\App\Models\DefectReason::where('name', 'LIKE', '%(2-Col Test)')->delete();
echo "\nCleanup completed successfully.\n";
