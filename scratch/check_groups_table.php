<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = Schema::getColumnListing('groups');
echo "Columns in 'groups' table:\n";
print_r($columns);

$groups = DB::table('groups')->get();
echo "\nRecords in 'groups':\n";
print_r($groups->toArray());
