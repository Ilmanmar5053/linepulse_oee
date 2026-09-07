<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DefectReason;

$dr = DefectReason::firstOrCreate(
    ['name' => 'Batang Rod Jig 2 Terproses'],
    ['defect_category_id' => 1, 'code' => 'DEF-TEST-01']
);

$dr->update([
    'countermeasure' => 'SOP Kaizen Engineering: Kalibrasi stopper sensor posisi jig 2 dan lakukan pemeriksaan clamping pneumatik per shift.'
]);

echo "Updated ID: {$dr->id}\n";
echo "Name: {$dr->name}\n";
echo "Countermeasure: {$dr->countermeasure}\n";
