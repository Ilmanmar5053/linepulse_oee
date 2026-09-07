<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NgRecord;
use App\Models\NgRecordItem;
use App\Models\ProductionRecord;
use App\Models\DefectReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Let's create test dummy NG records if needed so we have rich real-life data for testing Pareto charts!
echo "Checking existing NG Records and Items...\n";
$records = ProductionRecord::where('reject_quantity', '>', 0)->get();
echo "Total Production Records with Reject: " . $records->count() . "\n";

foreach ($records as $pr) {
    if (!$pr->ngRecord) {
        echo "Generating NG detail for Prod Record #{$pr->id} (Line: {$pr->production_line_id}, Reject: {$pr->reject_quantity})...\n";
        
        $ngAssy = (int) round($pr->reject_quantity * 0.5);
        $ngRod = (int) round($pr->reject_quantity * 0.3);
        $ngCap = $pr->reject_quantity - $ngAssy - $ngRod;

        $ng = NgRecord::create([
            'production_record_id' => $pr->id,
            'machine_id' => $pr->machine_id,
            'product_id' => $pr->product_id,
            'shift_id' => $pr->shift_id,
            'production_date' => $pr->production_date,
            'total_ng_target' => $pr->reject_quantity,
            'ng_assy' => $ngAssy,
            'assy_section' => 'Small End (Pin Bore)',
            'assy_reason' => 'Diameter Out of Spec',
            'ng_rod' => $ngRod,
            'rod_section' => 'Rod Body / Shank',
            'rod_reason' => 'Bending & Twist',
            'ng_cap' => $ngCap,
            'cap_section' => 'Joint / Serration Face',
            'cap_reason' => 'Surface Scratch & Dent',
            'total_ng_oee' => $pr->reject_quantity,
            'action_taken' => 'SCRAP',
            'inspector_name' => 'QC Inspector 1'
        ]);

        if ($ngAssy > 0) {
            $ng->items()->create([
                'component_type' => 'ASSY',
                'quantity' => $ngAssy,
                'section' => 'Small End (Pin Bore)',
                'reason' => 'Diameter Out of Spec'
            ]);
        }
        if ($ngRod > 0) {
            $ng->items()->create([
                'component_type' => 'ROD',
                'quantity' => $ngRod,
                'section' => 'Rod Body / Shank',
                'reason' => 'Bending & Twist'
            ]);
        }
        if ($ngCap > 0) {
            $ng->items()->create([
                'component_type' => 'CAP',
                'quantity' => $ngCap,
                'section' => 'Joint / Serration Face',
                'reason' => 'Surface Scratch & Dent'
            ]);
        }
    }
}

echo "NgRecord count now: " . NgRecord::count() . "\n";
echo "NgRecordItem count now: " . NgRecordItem::count() . "\n";
