<?php

namespace Database\Seeders;

use App\Enums\MachineStatus;
use App\Models\Machine;
use App\Models\MachineType;
use App\Models\Plant;
use App\Models\ProductionLine;
use App\Models\WorkCenter;
use Illuminate\Database\Seeder;

class ExtrusionMachineSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Default Plant & Area
        $plant = Plant::firstOrCreate(
            ['code' => 'PLANT-01'],
            ['name' => 'Main Manufacturing Plant', 'location' => 'Building A', 'is_active' => true]
        );

        $area = \App\Models\Area::firstOrCreate(
            ['name' => 'Main Extrusion Plant'],
            ['code' => 'AREA-01', 'plant_id' => $plant->id, 'is_active' => true]
        );

        // 2. Machine Type
        $machineType = MachineType::firstOrCreate(
            ['name' => 'Extruder Machine'],
            ['description' => 'Industrial Plastic/Metal Extrusion Line']
        );

        // 3. Lines EX-1 to EX-11
        $lines = ['EX-1', 'EX-2', 'EX-3', 'EX-4', 'EX-5', 'EX-6', 'EX-7', 'EX-8', 'EX-9', 'EX-10', 'EX-11'];
        $machineCapacities = ['15', '20', '30', '45', '60', '75', '90', '110', '130', '160', '190', '220', '250', '280', '300', '320'];

        $statuses = [
            MachineStatus::RUNNING,
            MachineStatus::RUNNING,
            MachineStatus::RUNNING,
            MachineStatus::IDLE,
            MachineStatus::BREAKDOWN,
        ];

        $statusIdx = 0;

        foreach ($lines as $lineCode) {
            $line = ProductionLine::firstOrCreate(
                ['code' => $lineCode],
                [
                    'area_id' => $area->id,
                    'name' => "Production Line {$lineCode}",
                    'target_oee' => 85.00,
                    'is_active' => true,
                ]
            );

            $wc = WorkCenter::firstOrCreate(
                ['code' => "WC-{$lineCode}"],
                [
                    'production_line_id' => $line->id,
                    'name' => "Work Center {$lineCode}",
                ]
            );

            foreach ($machineCapacities as $cap) {
                $mCode = "MC-{$lineCode}-{$cap}";
                $mName = "Extruder EX-{$cap}";
                $status = $statuses[$statusIdx % count($statuses)];
                $statusIdx++;

                Machine::updateOrCreate(
                    ['code' => $mCode],
                    [
                        'work_center_id' => $wc->id,
                        'machine_type_id' => $machineType->id,
                        'name' => $mName,
                        'serial_number' => "SN-{$lineCode}-{$cap}",
                        'status' => $status,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
