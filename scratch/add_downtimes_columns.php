<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== ADDING COLUMNS TO DOWNTIMES TABLE ===\n";

Schema::table('downtimes', function (Blueprint $table) {
    if (!Schema::hasColumn('downtimes', 'shift_id')) {
        $table->foreignId('shift_id')->nullable()->after('production_line_id')->constrained('shifts')->nullOnDelete();
    }
    if (!Schema::hasColumn('downtimes', 'product_id')) {
        $table->foreignId('product_id')->nullable()->after('shift_id')->constrained('products')->nullOnDelete();
    }
    if (!Schema::hasColumn('downtimes', 'team')) {
        $table->string('team')->nullable()->after('product_id');
    }
    if (!Schema::hasColumn('downtimes', 'problem_type')) {
        $table->string('problem_type')->nullable()->after('team');
    }
    if (!Schema::hasColumn('downtimes', 'action_taken')) {
        $table->text('action_taken')->nullable()->after('description');
    }
});

echo "Columns added successfully to downtimes table!\n";
