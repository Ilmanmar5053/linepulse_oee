<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use App\Models\ProductionLine;

echo "Creating groups table if not exists...\n";

if (!Schema::hasTable('groups')) {
    Schema::create('groups', function (Blueprint $table) {
        $table->id();
        $table->string('team', 30); // Team A, Team B, Team C
        $table->string('leader_name'); // Nama Leader / PIC
        $table->foreignId('production_line_id')->constrained('production_lines')->cascadeOnDelete();
        $table->timestamps();
        $table->unique(['production_line_id', 'team']);
    });
    echo "Table 'groups' created successfully.\n";
} else {
    echo "Table 'groups' already exists.\n";
}

// Ensure default groups are seeded for all lines
$lines = ProductionLine::all();
$defaultTeams = [
    ['team' => 'Team A', 'leader_name' => 'Budi Santoso'],
    ['team' => 'Team B', 'leader_name' => 'Agus Setiawan'],
    ['team' => 'Team C', 'leader_name' => 'Bambang Haryanto'],
];

foreach ($lines as $line) {
    foreach ($defaultTeams as $dt) {
        DB::table('groups')->updateOrInsert(
            [
                'production_line_id' => $line->id,
                'team' => $dt['team'],
            ],
            [
                'leader_name' => $dt['leader_name'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}

echo "Groups seeded successfully. Total groups: " . DB::table('groups')->count() . "\n";
