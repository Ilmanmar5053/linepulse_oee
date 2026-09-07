<?php

namespace Database\Seeders;

use App\Models\DefectCategory;
use App\Models\DefectReason;
use App\Models\NgSection;
use Illuminate\Database\Seeder;

class DefectBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Lokasi Master Data:
     * 1. Bagian NG           -> Tabel `ng_sections`
     * 2. Kategori Penyebab   -> Tabel `defect_categories`
     * 3. Penyebab / Remark   -> Tabel `defect_reasons`
     */
    public function run(): void
    {
        /* =========================================================================
         * 1. MASTER BANK DATA: BAGIAN NG (LOKASI / AREA CACAT PADA KOMPONEN)
         * Tabel Database: `ng_sections`
         * ========================================================================= */
        $sections = [
            [
                'code' => 'SEC-PIN-BORE',
                'name' => 'Small End (Pin Bore)',
                'component_type' => 'ALL',
                'description' => 'Area lubang pin piston / small end connecting rod'
            ],
            [
                'code' => 'SEC-CRANK-BORE',
                'name' => 'Big End (Crank Bore)',
                'component_type' => 'ALL',
                'description' => 'Area lubang kruk as / big end connecting rod'
            ],
            [
                'code' => 'SEC-ROD-BODY',
                'name' => 'Rod Body (I-Beam)',
                'component_type' => 'ROD',
                'description' => 'Batang tubuh conrod profil I-beam'
            ],
            [
                'code' => 'SEC-CAP-BODY',
                'name' => 'Cap Body',
                'component_type' => 'CAP',
                'description' => 'Badan tutup conrod (cap)'
            ],
            [
                'code' => 'SEC-JOINT-FACE',
                'name' => 'Joint Face / Serration',
                'component_type' => 'ALL',
                'description' => 'Permukaan sambungan split antara Rod dan Cap (Serration/Fracture Split)'
            ],
            [
                'code' => 'SEC-SIDE-FACE',
                'name' => 'Side Face / Thrust Width',
                'component_type' => 'ALL',
                'description' => 'Permukaan samping bidang gesek thrust width'
            ],
            [
                'code' => 'SEC-BOLT-HOLE',
                'name' => 'Bolt Hole & Thread',
                'component_type' => 'ALL',
                'description' => 'Lubang baut dan ulir drat penghubung conrod'
            ],
            [
                'code' => 'SEC-OIL-HOLE',
                'name' => 'Oil Hole (Lubrication)',
                'component_type' => 'ALL',
                'description' => 'Lubang jalur sirkulasi oli pelumasan'
            ],
            [
                'code' => 'SEC-BUSH-AREA',
                'name' => 'Bushing Press Fit Area',
                'component_type' => 'ROD',
                'description' => 'Area dudukan press fit bushing pada small end'
            ],
            [
                'code' => 'SEC-ASSY-FIT',
                'name' => 'Assembly Fitment / Rakitan',
                'component_type' => 'ASSY',
                'description' => 'Kesesuaian rakitan keseluruhan unit conrod'
            ],
        ];

        foreach ($sections as $s) {
            NgSection::updateOrCreate(
                ['code' => $s['code']],
                [
                    'name' => $s['name'],
                    'component_type' => $s['component_type'],
                    'description' => $s['description'],
                    'is_active' => true,
                ]
            );
        }

        /* =========================================================================
         * 2. MASTER BANK DATA: KATEGORI & PENYEBAB DEFECT / REMARK
         * Tabel Database: `defect_categories` & `defect_reasons`
         * ========================================================================= */
        $categories = [
            'DIMENSIONAL' => [
                'name' => 'Dimensi & Geometri',
                'reasons' => [
                    ['code' => 'DEF-SIZE-PIN', 'name' => 'Out of Tolerance Small End / Pin Bore'],
                    ['code' => 'DEF-SIZE-CRANK', 'name' => 'Out of Tolerance Big End / Crank Bore'],
                    ['code' => 'DEF-THICKNESS', 'name' => 'Ketebalan / Thrust Width Out of Spec'],
                    ['code' => 'DEF-WARP', 'name' => 'Deformasi / Part Bengkok (Warpage)'],
                    ['code' => 'DEF-ALIGN', 'name' => 'Misalignment / Sudut Kemiringan Tidak Pas'],
                    ['code' => 'DEF-CENTER-DIST', 'name' => 'Jarak Sumbu Center to Center Out'],
                ]
            ],
            'SURFACE' => [
                'name' => 'Kualitas Permukaan & Visual',
                'reasons' => [
                    ['code' => 'DEF-SCRATCH', 'name' => 'Goresan Permukaan (Surface Scratch & Dent)'],
                    ['code' => 'DEF-BURR', 'name' => 'Sisa Gram & Flashing Kasar (Excess Burr)'],
                    ['code' => 'DEF-ROUGH', 'name' => 'Kekasaran Permukaan Tinggi (Rough Surface)'],
                    ['code' => 'DEF-TOOL-MARK', 'name' => 'Bekas Pahat / Getaran Mesin (Tool Mark / Chatter)'],
                    ['code' => 'DEF-RUST', 'name' => 'Karat & Oksidasi Permukaan (Rust / Corrosion)'],
                ]
            ],
            'MATERIAL' => [
                'name' => 'Cacat Material & Cor (Foundry)',
                'reasons' => [
                    ['code' => 'DEF-POROSITY', 'name' => 'Porositas & Lubang Angin (Porosity / Pin Hole)'],
                    ['code' => 'DEF-CRACK', 'name' => 'Retak / Keretakan Mikro (Crack / Fracture)'],
                    ['code' => 'DEF-HARDNESS', 'name' => 'Kekerasan Material Out of Spec (Hardness NG)'],
                    ['code' => 'DEF-INCLUSION', 'name' => 'Inklusi Non-Logam / Kotoran Bahan'],
                ]
            ],
            'PROCESS' => [
                'name' => 'Proses Machining & Assembly',
                'reasons' => [
                    ['code' => 'DEF-THREAD', 'name' => 'Ulir Drat Baut Rusak / Slek (Damaged Thread)'],
                    ['code' => 'DEF-JOINT', 'name' => 'Sambungan Serration / Joint Face Rusak'],
                    ['code' => 'DEF-OIL-HOLE', 'name' => 'Lubang Oli Tersumbat / Meleset (Oil Hole NG)'],
                    ['code' => 'DEF-BUSH-PRESS', 'name' => 'Bushing Miring / Press Fitment NG'],
                    ['code' => 'DEF-TORQUE', 'name' => 'Torsi Baut Kurang / Berlebih (Torque NG)'],
                ]
            ],
        ];

        foreach ($categories as $catCode => $catData) {
            $cat = DefectCategory::updateOrCreate(
                ['code' => $catCode],
                ['name' => $catData['name']]
            );

            foreach ($catData['reasons'] as $r) {
                DefectReason::updateOrCreate(
                    ['code' => $r['code']],
                    ['defect_category_id' => $cat->id, 'name' => $r['name']]
                );
            }
        }
    }
}
