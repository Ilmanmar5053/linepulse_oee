<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

echo "=== ROLES IN DATABASE ===\n";
$roles = Role::all();
foreach ($roles as $r) {
    echo "ID: {$r->id}, Name: {$r->name}, Display: {$r->display_name}\n";
}

echo "\n=== USERS IN DATABASE ===\n";
$users = User::with('roles')->get();
foreach ($users as $u) {
    $roleNames = $u->roles->pluck('name')->join(', ');
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Roles: [{$roleNames}], Active: {$u->is_active}\n";
}
