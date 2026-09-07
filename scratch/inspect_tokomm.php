<?php

require 'd:/Project Aplikasi/Toko MM/vendor/autoload.php';
$app = require_once 'd:/Project Aplikasi/Toko MM/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== BRANCHES ===\n";
if (Schema::hasTable('branches')) {
    $branches = DB::table('branches')->get();
    foreach ($branches as $b) {
        echo "ID: {$b->id} | Name: " . ($b->name ?? $b->nama ?? 'N/A') . "\n";
    }
} elseif (Schema::hasTable('cabangs')) {
    $branches = DB::table('cabangs')->get();
    foreach ($branches as $b) {
        echo "ID: {$b->id} | Name: " . ($b->name ?? $b->nama ?? $b->nama_cabang ?? 'N/A') . "\n";
    }
} else {
    echo "No branches/cabangs table found. Listing all tables:\n";
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
    foreach ($tables as $t) {
        echo "- " . $t->name . "\n";
    }
}

echo "\n=== PRODUCTS ===\n";
if (Schema::hasTable('products')) {
    echo "Total products: " . DB::table('products')->count() . "\n";
} elseif (Schema::hasTable('barangs')) {
    echo "Total barangs: " . DB::table('barangs')->count() . "\n";
}
