<?php

namespace Database\Seeders;

use App\Models\DowntimeCategory;
use Illuminate\Database\Seeder;

class ProblemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'CAT-MECH',
                'name' => 'Problem Mesin Mekanik',
                'is_planned' => false,
                'description' => 'Trouble mekanikal, jam, komponen aus, spindle, pneumatik, hidrolik',
            ],
            [
                'code' => 'CAT-ELEC',
                'name' => 'Problem Mesin Elektrik',
                'is_planned' => false,
                'description' => 'Trouble elektrikal, PLC, sensor error, wiring, motor drive, inverter',
            ],
            [
                'code' => 'CAT-TOOL',
                'name' => 'Problem Tool',
                'is_planned' => false,
                'description' => 'Kerusakan tooling, die, jig, insert patah/aus, reset mold/cutter',
            ],
            [
                'code' => 'CAT-MAT',
                'name' => 'Material',
                'is_planned' => false,
                'description' => 'Keterlambatan pasokan raw material, material kosong, part NG incoming',
            ],
            [
                'code' => 'CAT-PLAN',
                'name' => 'Planning Downtime',
                'is_planned' => true,
                'description' => 'Perawatan berkala terjadwal, TPM, 5S, dandori / setup terjadwal',
            ],
            [
                'code' => 'CAT-INV',
                'name' => 'Inventory',
                'is_planned' => false,
                'description' => 'Kendala stok, transfer WIP antar proses, buffer line penuh/kosong',
            ],
            [
                'code' => 'CAT-OTH',
                'name' => 'Others',
                'is_planned' => false,
                'description' => 'Trouble lain-lain di luar kategori utama, utilitas, briefing',
            ],
        ];

        foreach ($categories as $cat) {
            DowntimeCategory::updateOrCreate(
                ['code' => $cat['code']],
                $cat
            );
        }
    }
}
