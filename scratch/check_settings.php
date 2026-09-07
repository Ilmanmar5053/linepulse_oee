<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemSetting;

$val = SystemSetting::get('company_profile');
$decoded = is_array($val) ? $val : json_decode($val, true);

echo "Decoded logo length: " . strlen($decoded['company_logo'] ?? '') . "\n";
echo "Decoded logo first 100 chars: " . substr($decoded['company_logo'] ?? '', 0, 100) . "\n";
echo "Decoded logo last 100 chars: " . substr($decoded['company_logo'] ?? '', -100) . "\n";
