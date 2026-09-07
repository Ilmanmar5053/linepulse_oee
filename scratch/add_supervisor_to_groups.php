<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!Schema::hasColumn('groups', 'supervisor_name')) {
    DB::statement("ALTER TABLE `groups` ADD COLUMN `supervisor_name` VARCHAR(255) NULL AFTER `leader_name`");
    echo "Added supervisor_name column to groups table.\n";
} else {
    echo "supervisor_name column already exists in groups table.\n";
}

// Populate sample supervisors for existing teams
DB::table('groups')->where('team', 'Team A')->update(['supervisor_name' => 'Alim Utama']);
DB::table('groups')->where('team', 'Team B')->update(['supervisor_name' => 'Hendra Wijaya']);
DB::table('groups')->where('team', 'Team C')->update(['supervisor_name' => 'Surya Pratama']);
DB::table('groups')->whereNull('supervisor_name')->update(['supervisor_name' => 'Alim Utama']);

echo "Updated groups records with supervisors:\n";
$groups = DB::table('groups')->take(6)->get();
print_r($groups->toArray());
