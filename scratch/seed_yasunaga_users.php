<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$usersList = [
    [
        'name' => 'Super Administrator',
        'email' => 'superadmin@prodcr.yasunaga.com',
        'role' => 'super_admin',
    ],
    [
        'name' => 'System Admin',
        'email' => 'admin@prodcr.yasunaga.com',
        'role' => 'admin',
    ],
    [
        'name' => 'Plant Manager',
        'email' => 'manager@prodcr.yasunaga.com',
        'role' => 'production_manager',
    ],
    [
        'name' => 'Production Supervisor',
        'email' => 'supervisor@prodcr.yasunaga.com',
        'role' => 'production_supervisor',
    ],
    [
        'name' => 'Alim Utama',
        'email' => 'alim.utama@prodcr.yasunaga.com',
        'role' => 'production_supervisor',
    ],
    [
        'name' => 'Agus Setiawan',
        'email' => 'agus.setiawan@prodcr.yasunaga.com',
        'role' => 'production_leader',
    ],
    [
        'name' => 'Budi Santoso',
        'email' => 'budi.santoso@prodcr.yasunaga.com',
        'role' => 'production_leader',
    ],
    [
        'name' => 'Bambang Haryanto',
        'email' => 'bambang.haryanto@prodcr.yasunaga.com',
        'role' => 'production_leader',
    ],
    [
        'name' => 'Quality Control Inspector',
        'email' => 'qc@prodcr.yasunaga.com',
        'role' => 'quality_control',
    ],
    [
        'name' => 'Maintenance Engineer',
        'email' => 'maintenance@prodcr.yasunaga.com',
        'role' => 'maintenance',
    ],
    [
        'name' => 'Lead Operator',
        'email' => 'operator@prodcr.yasunaga.com',
        'role' => 'operator',
    ],
];

echo "Seeding/Updating Users with domain @prodcr.yasunaga.com and default password 'password'...\n";

foreach ($usersList as $item) {
    $user = User::updateOrCreate(
        ['email' => $item['email']],
        [
            'name' => $item['name'],
            'password' => Hash::make('password'),
            'is_active' => true,
        ]
    );

    $role = Role::where('name', $item['role'])->first();
    if ($role) {
        $user->roles()->sync([$role->id]);
    }
    echo "Created/Updated User: {$user->name} <{$user->email}> [Role: {$item['role']}]\n";
}

// Also update admin@oeesys.com etc. if needed
$oldAdmin = User::where('email', 'admin@oeesys.com')->first();
if ($oldAdmin) {
    $oldAdmin->password = Hash::make('password');
    $oldAdmin->save();
}

echo "\nTotal users in system: " . User::count() . "\n";
