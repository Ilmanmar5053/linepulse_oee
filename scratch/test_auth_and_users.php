<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;

$auth = app(AuthController::class);
$userCtrl = app(UserController::class);

echo "=== Test 1: Login with full email 'superadmin@prodcr.yasunaga.com' ===\n";
$req1 = Request::create('/api/v1/auth/login', 'POST', [
    'email' => 'superadmin@prodcr.yasunaga.com',
    'password' => 'password'
]);
$res1 = $auth->login($req1);
$data1 = json_decode($res1->getContent(), true);
echo "Status: " . ($data1['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "User: " . $data1['data']['user']['name'] . " | Role: " . implode(', ', $data1['data']['user']['roles']) . "\n";

echo "\n=== Test 2: Login with short username 'manager' ===\n";
$req2 = Request::create('/api/v1/auth/login', 'POST', [
    'email' => 'manager',
    'password' => 'password'
]);
$res2 = $auth->login($req2);
$data2 = json_decode($res2->getContent(), true);
echo "Status: " . ($data2['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "User: " . $data2['data']['user']['name'] . " | Role: " . implode(', ', $data2['data']['user']['roles']) . "\n";

echo "\n=== Test 3: Login with short username 'operator' ===\n";
$req3 = Request::create('/api/v1/auth/login', 'POST', [
    'email' => 'operator',
    'password' => 'password'
]);
$res3 = $auth->login($req3);
$data3 = json_decode($res3->getContent(), true);
echo "Status: " . ($data3['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "User: " . $data3['data']['user']['name'] . " | Role: " . implode(', ', $data3['data']['user']['roles']) . "\n";

echo "\n=== Test 4: Generate Default Users ===\n";
$res4 = $userCtrl->generateDefaultUsers();
$data4 = json_decode($res4->getContent(), true);
echo "Status: " . ($data4['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $data4['message'] . "\n";
