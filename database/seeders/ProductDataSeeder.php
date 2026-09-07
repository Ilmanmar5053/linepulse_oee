<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductionLine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductDataSeeder extends Seeder
{
    public function run(): void
    {
        $lineData = [
            'FX-1' => [
                ['1T071-220512', 'RT140-SKC', 77],
                ['13201-31010YI', '12R', 122.8],
                ['13201-32010YI', '1GZ', 90],
                ['13201-35020YI', '22R', 105],
                ['13201-75020YI', '3RZ', 83],
                ['13201-74070UYI', '3SFE', 113.6],
                ['13201-74912YI', '3SG', 113.6],
                ['13201-62020YI', '3VZ', 120],
                ['13201-131010YI', '5K', 118],
                ['13201-74032YI', '5S FE', 83],
            ],
            'FX-2' => [
                ['14921-22053', 'E9', 77],
                ['11131-22055', 'EA8', 77],
                ['1T051-220511', 'RT100/120', 77],
                ['14911-22053', 'E7/8', 39.6],
                ['MD050006V', '4D5-INA', 30.5],
                ['13201-BZ070', 'ED-ADM', 30.5],
                ['13201-B2040MYI', 'ED-YC', 30.5],
                ['13201-BZ080', 'EF-ADM', 30.5],
                ['13201-B2030MYI', 'EF-YC', 30.5],
                ['1210A4LC0A', 'HR12', 34],
            ],
            'FX-3' => [
                ['1T021-220511', 'RT80/90', 39.6],
                ['31A1901022G', 'SL-L', 31.6],
                ['31A1910023G', 'SL-S', 31.6],
                ['13201-BZ040YI', 'KR-YC', 22.5],
                ['13201-21040YI', '2NZ', 22.5],
                ['13201-21040CKDYI', '2NZ-CKD', 22.5],
                ['13201-0Y070 ADM', 'FX3-1NR-ADM', 22.5],
                ['13201-0Y070 TMMIN', 'FX3-1NR-TMMIN', 22.5],
                ['13201-0Y080 ADM', 'FX3-2NR-ADM', 22.5],
                ['13201-0Y080 TMMIN', 'FX3-2NR-TMMIN', 22.5],
            ],
            'FX-4' => [
                ['13201-BZ040', 'KR-ADM', 22.5],
                ['13201-OC010', '1TR', 22.5],
                ['13201-OC020', '2TR', 22.5],
                ['13201-OC020 EXP', '2TR-EXP', 22.5],
                ['1115A387TAYI', '4N14', 40],
            ],
            'FX-5' => [
                ['13201-33030YIGG', '1ND', 31.2],
                ['13201-21051YI', '1NZ', 22.5],
                ['1115A384YI', '4N13', 34],
                ['1115A481', '4N14-4X45', 35.7],
                ['13201-21051DYI', '604F-KR', 22.5],
            ],
            'FX-6' => [
                ['13201-BZ061', '3SZ-HL', 22.5],
                ['13201-BZ060-00-87', '3SZ', 22.5],
                ['13201-BZ060YI', 'K3-HL', 22.5],
                ['13201-BZ011', 'K3', 22.5],
                ['13201-BZ060', '3SZ', 22.5],
            ],
            'FX-7' => [
                ['13201-0Y070 FX7 ADM', 'FX7-1NR-ADM', 21],
                ['13201-0Y070 FX7 TMMIN', 'FX7-1NR-TMMIN', 21],
                ['13201-0Y080 FX7 ADM', 'FX7-2NR-ADM', 21],
                ['13201-0Y080 FX7 TMMIN', 'FX7-2NR-TMMIN', 21],
            ],
            'FX-8' => [
                ['13201-36060', '8AR', 31.2],
                ['13201-38042YI', '2UR-FSE', 31.2],
                ['13201-38050', '2UR-GSE', 37.8],
                ['13201-38020YI', '3UR', 28],
                ['13201-31040-D', '4GR', 31.2],
            ],
            'FX-9' => [
                ['13201-0Y070 FX9 ADM', 'FX9-1NR-ADM', 21],
                ['13201-0Y070 FX9 TMMIN', 'FX9-1NR-TMMIN', 21],
                ['13201-0Y080 FX9 ADM', 'FX9-2NR-ADM', 21],
                ['13201-0Y080 FX9 TMMIN', 'FX9-2NR-TMMIN', 21],
                ['13201-OY080-00-87', 'D81F', 21],
            ],
            'FX-10' => [
                ['13201-0Y070 OP10 ADM', 'OP10-1NR-ADM', 5.5],
                ['13201-0Y070 OP10 TMMIN', 'OP10-1NR-TMMIN', 5.5],
                ['13201-0Y080 OP10 ADM', 'OP10-2NR-ADM', 5.5],
                ['13201-0Y080 OP10 TMMIN', 'OP10-2NR-TMMIN', 5.5],
                ['1210A4LC0A 5', 'HR12 (5.5s)', 5.5],
            ],
            'FX-11' => [
                ['13201-0Y070 FX11 ADM', 'FX11-1NR-ADM', 21],
                ['13201-0Y070 FX11 TMMIN', 'FX11-1NR-TMMIN', 21],
                ['13201-0Y080 FX11 ADM', 'FX11-2NR-ADM', 21],
                ['13201-0Y080 FX11 TMMIN', 'FX11-2NR-TMMIN', 21],
                ['1210A4LC0A 97', 'HR12 (97.5s)', 97.5],
            ],
        ];

        // Fetch lines map
        $lineMap = [];
        foreach (ProductionLine::all() as $l) {
            $lineMap[strtoupper(trim($l->code))] = $l->id;
            $lineMap[strtoupper(trim($l->name))] = $l->id;
        }

        $usedSkus = [];

        foreach ($lineData as $lineCode => $products) {
            $lineId = $lineMap[$lineCode] ?? null;

            foreach ($products as $item) {
                $sku = trim($item[0]);
                $name = trim($item[1]);
                $cycleTime = (float) $item[2];

                if (isset($usedSkus[$sku])) {
                    $uniqueSku = $sku . '-' . strtoupper(str_replace(' ', '', $name));
                } else {
                    $uniqueSku = $sku;
                }
                $usedSkus[$uniqueSku] = true;

                Product::updateOrCreate(
                    ['sku' => $uniqueSku],
                    [
                        'name' => $name,
                        'production_line_id' => $lineId,
                        'ideal_cycle_time' => $cycleTime,
                        'unit_of_measure' => 'PCS',
                    ]
                );
            }
        }
    }
}
