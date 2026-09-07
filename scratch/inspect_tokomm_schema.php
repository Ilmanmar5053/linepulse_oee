<?php

require 'd:/Project Aplikasi/Toko MM/vendor/autoload.php';
$app = require_once 'd:/Project Aplikasi/Toko MM/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== TABLES ===\n";
$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
foreach ($tables as $t) {
    echo "- " . $t->name . "\n";
}

echo "\n=== CABANGS DATA ===\n";
$cabangs = DB::table('cabangs')->get();
print_r($cabangs);

echo "\n=== BARANGS SCHEMA & SAMPLE ===\n";
$barangs = DB::table('barangs')->get();
print_r($barangs);
