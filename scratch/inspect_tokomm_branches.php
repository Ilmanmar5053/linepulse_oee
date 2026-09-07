<?php

require 'd:/Project Aplikasi/Toko MM/vendor/autoload.php';
$app = require_once 'd:/Project Aplikasi/Toko MM/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== BRANCHES ===\n";
$branches = DB::table('branches')->get();
foreach ($branches as $b) {
    echo json_encode($b) . "\n";
}

echo "\n=== BARANGS ===\n";
$barangs = DB::table('barangs')->get();
foreach ($barangs as $brg) {
    echo "ID: {$brg->id} | SKU: " . ($brg->kode_barang ?? $brg->sku ?? '-') . " | Name: " . ($brg->nama_barang ?? $brg->name ?? '-') . " | Stok Total/Default: " . ($brg->stok ?? $brg->stock ?? 0) . "\n";
}

echo "\n=== BRANCH STOCKS ===\n";
$stocks = DB::table('branch_stocks')->get();
foreach ($stocks as $st) {
    echo json_encode($st) . "\n";
}
