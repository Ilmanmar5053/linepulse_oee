<?php

namespace Database\Seeders;

use App\Enums\MachineStatus;
use App\Models\Area;
use App\Models\Machine;
use App\Models\MachineType;
use App\Models\Plant;
use App\Models\ProductionLine;
use App\Models\WorkCenter;
use Illuminate\Database\Seeder;

class FlexibleMachineSeeder extends Seeder
{
    public function run(): void
    {
        $rawText = <<<'DATA'
MC-FX-110	OP-10	FX-1
MC-FX-120	OP-20	FX-1
MC-FX-125	OP-25	FX-1
MC-FX-130	OP-30	FX-1
MC-FX-140	OP-40	FX-1
MC-FX-150	OP-50	FX-1
MC-FX-160	OP-60	FX-1
MC-FX-170	OP-70	FX-1
MC-FX-180	OP-80	FX-1
MC-FX-185	OP-85	FX-1
MC-FX-190	OP-90	FX-1
MC-FX-195	OP-95	FX-1
MC-FX-1100	OP-100	FX-1
MC-FX-1120	OP-120	FX-1
MC-FX-1130	OP-130	FX-1
MC-FX-1140	OP-140	FX-1
MC-FX-1150	OP-150	FX-1
MC-FX-1160	OP-160	FX-1
MC-FX-1170	OP-170	FX-1
MC-FX-1190	OP-190	FX-1
MC-FX-1200	OP-200	FX-1
MC-FX-1210	OP-210	FX-1
MC-FX-1120	OP-20	FX-11
MC-FX-1130	OP-30	FX-11
MC-FX-1140	OP-40	FX-11
MC-FX-1145	OP-45	FX-11
MC-FX-1150	OP-50	FX-11
MC-FX-1160	OP-60	FX-11
MC-FX-1170	OP-70	FX-11
MC-FX-1180	OP-80	FX-11
MC-FX-1190	OP-90	FX-11
MC-FX-11100	OP-100	FX-11
MC-FX-11120	OP-120	FX-11
MC-FX-11130	OP-130	FX-11
MC-FX-11140	OP-140	FX-11
MC-FX-11150	OP-150	FX-11
MC-FX-11160	OP-160	FX-11
MC-FX-11170	OP-170	FX-11
MC-FX-11180	OP-180	FX-11
MC-FX-11190	OP-190	FX-11
MC-FX-11210	OP-210	FX-11
MC-FX-11220	OP-220	FX-11
MC-FX-210	OP-10	FX-2
MC-FX-220,1	OP-20.1	FX-2
MC-FX-220,2	OP-20.2	FX-2
MC-FX-230	OP-30	FX-2
MC-FX-240	OP-40	FX-2
MC-FX-250	OP-50	FX-2
MC-FX-260,1	OP-60.1	FX-2
MC-FX-260,2	OP-60.2	FX-2
MC-FX-270	OP-70	FX-2
MC-FX-280	OP-80	FX-2
MC-FX-290	OP-90	FX-2
MC-FX-2100	OP-100	FX-2
MC-FX-2110	OP-110	FX-2
MC-FX-2120	OP-120	FX-2
MC-FX-2130	OP-130	FX-2
MC-FX-2140	OP-140	FX-2
MC-FX-2150	OP-150	FX-2
MC-FX-2160	OP-160	FX-2
MC-FX-2170	OP-170	FX-2
MC-FX-2180	OP-180	FX-2
MC-FX-2190	OP-190	FX-2
MC-FX-2200	OP-200	FX-2
MC-FX-2220	OP-220	FX-2
MC-FX-310	OP-10	FX-3
MC-FX-320,1	OP-20.1	FX-3
MC-FX-320,2	OP-20.2	FX-3
MC-FX-330	OP-30	FX-3
MC-FX-335	OP-35	FX-3
MC-FX-340	OP-40	FX-3
MC-FX-350	OP-50	FX-3
MC-FX-360	OP-60	FX-3
MC-FX-370	OP-70	FX-3
MC-FX-380	OP-80	FX-3
MC-FX-390	OP-90	FX-3
MC-FX-3100	OP-100	FX-3
MC-FX-3130	OP-130	FX-3
MC-FX-3140	OP-140	FX-3
MC-FX-3150	OP-150	FX-3
MC-FX-3160	OP-160	FX-3
MC-FX-3170	OP-170	FX-3
MC-FX-3180	OP-180	FX-3
MC-FX-3190	OP-190	FX-3
MC-FX-3200	OP-200	FX-3
MC-FX-3210	OP-210	FX-3
MC-FX-3220	OP-220	FX-3
MC-FX-410	OP-10	FX-4
MC-FX-420	OP-20	FX-4
MC-FX-425	OP-25	FX-4
MC-FX-430	OP-30	FX-4
MC-FX-440	OP-40	FX-4
MC-FX-450	OP-50	FX-4
MC-FX-460	OP-60	FX-4
MC-FX-470	OP-70	FX-4
MC-FX-480	OP-80	FX-4
MC-FX-490	OP-90	FX-4
MC-FX-4100	OP-100	FX-4
MC-FX-4110	OP-110	FX-4
MC-FX-4130,1	OP-130.1	FX-4
MC-FX-4130,2	OP-130.2	FX-4
MC-FX-4138	OP-138	FX-4
MC-FX-4140	OP-140	FX-4
MC-FX-4150	OP-150	FX-4
MC-FX-4160	OP-160	FX-4
MC-FX-4170	OP-170	FX-4
MC-FX-4180	OP-180	FX-4
MC-FX-4190	OP-190	FX-4
MC-FX-4200	OP-200	FX-4
MC-FX-4210	OP-210	FX-4
MC-FX-4220	OP-220	FX-4
MC-FX-510	OP-10	FX-5
MC-FX-520	OP-20	FX-5
MC-FX-530	OP-30	FX-5
MC-FX-540	OP-40	FX-5
MC-FX-550	OP-50	FX-5
MC-FX-560	OP-60	FX-5
MC-FX-570	OP-70	FX-5
MC-FX-580	OP-80	FX-5
MC-FX-590	OP-90	FX-5
MC-FX-5100	OP-100	FX-5
MC-FX-5110	OP-110	FX-5
MC-FX-5120	OP-120	FX-5
MC-FX-5130	OP-130	FX-5
MC-FX-5140	OP-140	FX-5
MC-FX-5150	OP-150	FX-5
MC-FX-5153	OP-153	FX-5
MC-FX-5155	OP-155	FX-5
MC-FX-5160	OP-160	FX-5
MC-FX-5170	OP-170	FX-5
MC-FX-5180	OP-180	FX-5
MC-FX-5190	OP-190	FX-5
MC-FX-5210	OP-210	FX-5
MC-FX-5220	OP-220	FX-5
MC-FX-5230	OP-230	FX-5
MC-FX-5240	OP-240	FX-5
MC-FX-5250	OP-250	FX-5
MC-FX-610	OP-10	FX-6
MC-FX-615	OP-15	FX-6
MC-FX-620	OP-20	FX-6
MC-FX-630	OP-30	FX-6
MC-FX-640	OP-40	FX-6
MC-FX-650	OP-50	FX-6
MC-FX-660	OP-60	FX-6
MC-FX-670	OP-70	FX-6
MC-FX-680	OP-80	FX-6
MC-FX-690	OP-90	FX-6
MC-FX-6100	OP-100	FX-6
MC-FX-6110	OP-110	FX-6
MC-FX-6135	OP-135	FX-6
MC-FX-6140	OP-140	FX-6
MC-FX-6150	OP-150	FX-6
MC-FX-6160	OP-160	FX-6
MC-FX-6170	OP-170	FX-6
MC-FX-6190	OP-190	FX-6
MC-FX-6200	OP-200	FX-6
MC-FX-6210	OP-210	FX-6
MC-FX-6220	OP-220	FX-6
MC-FX-6230	OP-230	FX-6
MC-FX-720	OP-20	FX-7
MC-FX-730	OP-30	FX-7
MC-FX-740	OP-40	FX-7
MC-FX-745	OP-45	FX-7
MC-FX-750	OP-50	FX-7
MC-FX-760	OP-60	FX-7
MC-FX-770	OP-70	FX-7
MC-FX-780	OP-80	FX-7
MC-FX-790	OP-90	FX-7
MC-FX-7100	OP-100	FX-7
MC-FX-7120	OP-120	FX-7
MC-FX-7155	OP-155	FX-7
MC-FX-7160	OP-160	FX-7
MC-FX-7170	OP-170	FX-7
MC-FX-7180	OP-180	FX-7
MC-FX-7190	OP-190	FX-7
MC-FX-7210	OP-210	FX-7
MC-FX-7220	OP-220	FX-7
MC-FX-7240	OP-240	FX-7
MC-FX-7250	OP-250	FX-7
MC-FX-810	OP-10	FX-8
MC-FX-820	OP-20	FX-8
MC-FX-830	OP-30	FX-8
MC-FX-840	OP-40	FX-8
MC-FX-850	OP-50	FX-8
MC-FX-860	OP-60	FX-8
MC-FX-870	OP-70	FX-8
MC-FX-880	OP-80	FX-8
MC-FX-890	OP-90	FX-8
MC-FX-8120	OP-120	FX-8
MC-FX-8130	OP-130	FX-8
MC-FX-8140	OP-140	FX-8
MC-FX-8150	OP-150	FX-8
MC-FX-8160	OP-160	FX-8
MC-FX-8170	OP-170	FX-8
MC-FX-8180	OP-180	FX-8
MC-FX-8190	OP-190	FX-8
MC-FX-8200	OP-200	FX-8
MC-FX-8210	OP-210	FX-8
MC-FX-8220	OP-220	FX-8
MC-FX-8230	OP-230	FX-8
MC-FX-8240	OP-240	FX-8
MC-FX-920	OP-20	FX-9
MC-FX-930	OP-30	FX-9
MC-FX-940	OP-40	FX-9
MC-FX-945	OP-45	FX-9
MC-FX-950	OP-50	FX-9
MC-FX-960	OP-60	FX-9
MC-FX-970	OP-70	FX-9
MC-FX-980	OP-80	FX-9
MC-FX-990	OP-90	FX-9
MC-FX-9100	OP-100	FX-9
MC-FX-9120	OP-120	FX-9
MC-FX-9130	OP-130	FX-9
MC-FX-9140	OP-140	FX-9
MC-FX-9150	OP-150	FX-9
MC-FX-9160	OP-160	FX-9
MC-FX-9170	OP-170	FX-9
MC-FX-9180	OP-180	FX-9
MC-FX-9190	OP-190	FX-9
MC-FX-9210	OP-210	FX-9
MC-FX-9220	OP-220	FX-9
DATA;

        // 1. Ensure Default Plant & Area
        $plant = Plant::firstOrCreate(
            ['code' => 'PLANT-01'],
            ['name' => 'Main Manufacturing Plant', 'location' => 'Building A', 'is_active' => true]
        );

        $area = Area::firstOrCreate(
            ['name' => 'Flexible Packaging Area'],
            ['code' => 'AREA-FX', 'plant_id' => $plant->id, 'is_active' => true]
        );

        // 2. Machine Type
        $machineType = MachineType::firstOrCreate(
            ['name' => 'Flexo / Machine Operation'],
            ['description' => 'Flexible Convert & Operation Line']
        );

        // 3. Parse Data Lines
        $linesData = explode("\n", trim($rawText));
        $statuses = [
            MachineStatus::RUNNING,
            MachineStatus::RUNNING,
            MachineStatus::RUNNING,
            MachineStatus::IDLE,
            MachineStatus::BREAKDOWN,
        ];
        $idx = 0;

        foreach ($linesData as $row) {
            $cols = preg_split('/\s+/', trim($row));
            if (count($cols) < 3) continue;

            $code = trim($cols[0]);
            $name = trim($cols[1]);
            $lineCode = trim($cols[2]);

            // Production Line
            $line = ProductionLine::firstOrCreate(
                ['code' => $lineCode],
                [
                    'area_id' => $area->id,
                    'name' => $lineCode,
                    'target_oee' => 85.00,
                    'is_active' => true,
                ]
            );

            // Work Center
            $wc = WorkCenter::firstOrCreate(
                ['code' => "WC-{$lineCode}"],
                [
                    'production_line_id' => $line->id,
                    'name' => "Work Center {$lineCode}",
                ]
            );

            // Machine
            $status = $statuses[$idx % count($statuses)];
            $idx++;

            Machine::updateOrCreate(
                ['code' => $code],
                [
                    'work_center_id' => $wc->id,
                    'machine_type_id' => $machineType->id,
                    'name' => $name,
                    'serial_number' => "SN-{$code}",
                    'status' => $status,
                    'is_active' => true,
                ]
            );
        }
    }
}
