<?php

require 'd:/Project Aplikasi/Toko MM/vendor/autoload.php';
$app = require_once 'd:/Project Aplikasi/Toko MM/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$branchId = 2; // Cabang Banjarmasin
$now = Carbon::now();

$stockQuantities = [
    1 => 100, // Pempek Kapal Selam Telur Utuh
    2 => 75,  // Pempek Lenjer Besar
    3 => 150, // Pempek Adaan Gurih
    4 => 120, // Pempek Kulit Crispy
    5 => 30,  // Pempek Keriting
    6 => 50,  // Pempek Lenggang Goreng Telur
    7 => 60,  // Paket Sultan A
    8 => 35,  // Paket Sultan Jumbo
    9 => 20,  // Paket Sultan Family Box
    10 => 80, // Es Kacang Merah
    11 => 50, // Es Manisan Mangga
    12 => 250,// Teh Obeng / Es Teh
    13 => 60, // Botol Cuko Hitam
    14 => 120,// Extra Ebi & Timun
];

$barangs = DB::table('barangs')->get();
$updatedCount = 0;

foreach ($barangs as $brg) {
    $qty = $stockQuantities[$brg->id] ?? 50;
    $minStok = $brg->min_stok ?? 10;
    $status = ($qty <= 0) ? 'Habis' : (($qty <= $minStok) ? 'Menipis' : 'Tersedia');

    $existing = DB::table('branch_stocks')
        ->where('branch_id', $branchId)
        ->where('barang_id', $brg->id)
        ->first();

    if ($existing) {
        DB::table('branch_stocks')
            ->where('id', $existing->id)
            ->update([
                'stok' => $qty,
                'min_stok' => $minStok,
                'status' => $status,
                'updated_at' => $now,
            ]);
    } else {
        DB::table('branch_stocks')->insert([
            'branch_id' => $branchId,
            'barang_id' => $brg->id,
            'stok' => $qty,
            'min_stok' => $minStok,
            'status' => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
    $updatedCount++;
    echo "✓ Updated product ID {$brg->id} ({$brg->nama_barang}): Stock = {$qty}, Status = {$status}\n";
}

echo "\nSuccessfully bulk updated stock for {$updatedCount} products at Cabang Banjarmasin (branch_id = 2)!\n";
