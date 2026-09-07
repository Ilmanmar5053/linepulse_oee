<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemSetting;

$val = SystemSetting::get('company_profile');
$decoded = is_array($val) ? $val : json_decode($val, true);

echo "Company Name: " . ($decoded['company_name'] ?? 'None') . "\n";
echo "Plant Name: " . ($decoded['plant_name'] ?? 'None') . "\n";
echo "Plant Code: " . ($decoded['plant_code'] ?? 'None') . "\n";
echo "Company Logo exists: " . (!empty($decoded['company_logo']) ? 'YES (' . strlen($decoded['company_logo']) . ' chars)' : 'NO') . "\n";

// Save to scratch/test_logo.png if base64
if (!empty($decoded['company_logo'])) {
    $data = $decoded['company_logo'];
    if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
        $data = substr($data, strpos($data, ',') + 1);
    }
    $bin = base64_decode($data);
    file_put_contents(__DIR__ . '/test_logo.png', $bin);
    echo "Saved to scratch/test_logo.png (size: " . strlen($bin) . " bytes)\n";
}
