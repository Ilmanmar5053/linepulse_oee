<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DefectCategory;
use App\Models\DefectReason;
use App\Models\DowntimeCategory;
use App\Models\DowntimeReason;
use App\Models\Machine;
use App\Models\NgSection;
use App\Models\Plant;
use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\Shift;
use App\Models\SystemSetting;
use App\Models\WorkCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    // PLANTS
    public function plants(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Plant::with('areas.productionLines')->get(),
        ]);
    }

    public function storePlant(Request $request): JsonResponse
    {
        if (!$request->has('timezone') || empty($request->timezone)) {
            $request->merge(['timezone' => 'Asia/Jakarta']);
        }
        $validated = $request->validate([
            'code' => 'required|string|unique:plants,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'timezone' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $plant = Plant::create($validated);

        return response()->json(['success' => true, 'message' => 'Plant created successfully', 'data' => $plant], 201);
    }

    public function updatePlant(Request $request, $id): JsonResponse
    {
        $plant = Plant::findOrFail($id);
        $validated = $request->validate([
            'code' => 'required|string|unique:plants,code,' . $id,
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'timezone' => 'nullable|string',
        ]);

        $plant->update($validated);
        return response()->json(['success' => true, 'message' => 'Plant updated successfully', 'data' => $plant]);
    }

    public function destroyPlant($id): JsonResponse
    {
        $plant = Plant::findOrFail($id);
        $plant->delete();
        return response()->json(['success' => true, 'message' => 'Plant deleted successfully']);
    }

    // PRODUCTION LINES
    public function productionLines(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ProductionLine::with(['plant', 'area.plant'])->where('is_active', true)->get(),
        ]);
    }

    public function storeProductionLine(Request $request): JsonResponse
    {
        if (!$request->has('area_id') || empty($request->area_id)) {
            $request->merge(['area_id' => 1]);
        }
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'plant_id' => 'nullable|exists:plants,id',
            'code' => 'required|string|unique:production_lines,code',
            'name' => 'required|string|max:255',
            'target_oee' => 'required|numeric|between:0,100',
        ]);

        $line = ProductionLine::create($validated);

        return response()->json(['success' => true, 'message' => 'Production line created', 'data' => $line], 201);
    }

    public function updateProductionLine(Request $request, $id): JsonResponse
    {
        $line = ProductionLine::findOrFail($id);
        $validated = $request->validate([
            'plant_id' => 'nullable|exists:plants,id',
            'code' => 'required|string|unique:production_lines,code,' . $id,
            'name' => 'required|string|max:255',
            'target_oee' => 'nullable|numeric|between:0,100',
        ]);

        $line->update($validated);
        return response()->json(['success' => true, 'message' => 'Production line updated', 'data' => $line]);
    }

    public function destroyProductionLine($id): JsonResponse
    {
        $line = ProductionLine::findOrFail($id);
        $line->delete();
        return response()->json(['success' => true, 'message' => 'Production line deleted']);
    }

    // MACHINES
    public function machines(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Machine::with('workCenter.productionLine')->orderBy('code')->get(),
        ]);
    }

    public function storeMachine(Request $request): JsonResponse
    {
        $code = trim($request->input('code', ''));
        $name = trim($request->input('name', ''));
        $rawWcId = $request->input('work_center_id');
        $status = strtoupper($request->input('status', 'RUNNING'));

        // Resolve Work Center from work_center_id or production_line_id
        $resolvedWcId = null;
        if (!empty($rawWcId)) {
            $wc = WorkCenter::find($rawWcId);
            if (!$wc) {
                $wc = WorkCenter::where('production_line_id', $rawWcId)->first();
                if (!$wc) {
                    $line = ProductionLine::find($rawWcId);
                    $wc = WorkCenter::create([
                        'production_line_id' => $rawWcId,
                        'code' => 'WC-' . ($line ? $line->code : $rawWcId),
                        'name' => 'Work Center ' . ($line ? $line->name : $rawWcId),
                    ]);
                }
            }
            if ($wc) {
                $resolvedWcId = $wc->id;
            }
        }

        if (!$resolvedWcId) {
            $firstWc = WorkCenter::first();
            $resolvedWcId = $firstWc ? $firstWc->id : 17;
        }

        // Check if machine already exists (including soft-deleted)
        $existing = Machine::withTrashed()->where('code', $code)->first();
        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update([
                'name' => $name ?: $existing->name,
                'work_center_id' => $resolvedWcId,
                'status' => $status,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Machine record restored and updated successfully',
                'data' => $existing->fresh(['workCenter.productionLine']),
            ], 200);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string',
        ]);

        $machine = Machine::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'serial_number' => $validated['serial_number'] ?? null,
            'work_center_id' => $resolvedWcId,
            'status' => $status,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Machine created successfully',
            'data' => $machine->fresh(['workCenter.productionLine']),
        ], 201);
    }

    public function importMachines(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.code' => 'required|string',
            'items.*.name' => 'required|string',
        ]);

        $imported = [];
        foreach ($request->items as $item) {
            $code = trim($item['code']);
            $name = trim($item['name']);
            $rawWcId = $item['work_center_id'] ?? null;
            $resolvedWcId = null;

            if ($rawWcId) {
                $wc = WorkCenter::find($rawWcId) ?? WorkCenter::where('production_line_id', $rawWcId)->first();
                if ($wc) $resolvedWcId = $wc->id;
            }
            if (!$resolvedWcId) {
                $resolvedWcId = 17;
            }

            $machine = Machine::withTrashed()->where('code', $code)->first();
            if ($machine) {
                if ($machine->trashed()) $machine->restore();
                $machine->update([
                    'name' => $name,
                    'work_center_id' => $resolvedWcId,
                    'status' => $item['status'] ?? 'RUNNING',
                    'is_active' => true,
                ]);
            } else {
                $machine = Machine::create([
                    'code' => $code,
                    'name' => $name,
                    'work_center_id' => $resolvedWcId,
                    'status' => $item['status'] ?? 'RUNNING',
                    'is_active' => true,
                ]);
            }
            $imported[] = $machine;
        }

        return response()->json([
            'success' => true,
            'message' => count($imported) . ' machines imported and synchronized with database.',
            'data' => $imported,
        ]);
    }

    public function updateMachine(Request $request, $id): JsonResponse
    {
        $machine = Machine::withTrashed()->findOrFail($id);
        if ($machine->trashed()) {
            $machine->restore();
        }

        $code = $request->input('code');
        $name = $request->input('name');
        $rawWcId = $request->input('work_center_id');
        $status = $request->input('status');

        $updateData = [];
        if ($code) {
            $updateData['code'] = trim($code);
        }
        if ($name) {
            $updateData['name'] = trim($name);
        }
        if ($rawWcId) {
            $wc = WorkCenter::find($rawWcId) ?? WorkCenter::where('production_line_id', $rawWcId)->first();
            if ($wc) {
                $updateData['work_center_id'] = $wc->id;
            }
        }
        if ($status) {
            $st = strtoupper($status);
            if ($st === 'STOP') $st = 'STOPPED';
            $updateData['status'] = $st;
        }

        $updateData['is_active'] = true;
        $machine->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Machine updated successfully',
            'data' => $machine->fresh(['workCenter.productionLine']),
        ]);
    }

    public function destroyMachine($id): JsonResponse
    {
        $machine = Machine::findOrFail($id);
        $machine->delete();
        return response()->json(['success' => true, 'message' => 'Machine deleted']);
    }

    // PRODUCTS
    public function products(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Product::with(['productCategory', 'productionLine'])->get(),
        ]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        if (!$request->has('unit_of_measure') || empty($request->unit_of_measure)) {
            $request->merge(['unit_of_measure' => 'Pcs']);
        }
        $validated = $request->validate([
            'production_line_id' => 'nullable|exists:production_lines,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'unit_of_measure' => 'required|string',
            'ideal_cycle_time' => 'required|numeric|min:0.0001',
        ]);

        $product = Product::create($validated);

        return response()->json(['success' => true, 'message' => 'Product created', 'data' => $product->load(['productCategory', 'productionLine'])], 201);
    }

    public function updateProduct(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'production_line_id' => 'nullable|exists:production_lines,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'sku' => 'required|string|unique:products,sku,' . $id,
            'name' => 'required|string|max:255',
            'unit_of_measure' => 'nullable|string',
            'ideal_cycle_time' => 'nullable|numeric|min:0.0001',
        ]);

        $product->update($validated);
        return response()->json(['success' => true, 'message' => 'Product updated', 'data' => $product->fresh(['productCategory', 'productionLine'])]);
    }

    public function destroyProduct($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted']);
    }

    // DOWNTIME CATEGORIES (KATEGORI PROBLEM)
    public function downtimeCategories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => DowntimeCategory::orderBy('id')->get(),
        ]);
    }

    public function storeDowntimeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:downtime_categories,code',
            'name' => 'required|string|max:255',
            'is_planned' => 'nullable',
            'description' => 'nullable|string',
        ]);

        $validated['is_planned'] = $request->boolean('is_planned', false);
        $category = DowntimeCategory::create($validated);

        return response()->json(['success' => true, 'message' => 'Kategori Problem berhasil ditambahkan', 'data' => $category], 201);
    }

    public function updateDowntimeCategory(Request $request, $id): JsonResponse
    {
        $category = DowntimeCategory::findOrFail($id);
        $validated = $request->validate([
            'code' => 'required|string|unique:downtime_categories,code,' . $id,
            'name' => 'required|string|max:255',
            'is_planned' => 'nullable',
            'description' => 'nullable|string',
        ]);

        if ($request->has('is_planned')) {
            $validated['is_planned'] = $request->boolean('is_planned');
        }

        $category->update($validated);
        return response()->json(['success' => true, 'message' => 'Kategori Problem berhasil diperbarui', 'data' => $category]);
    }

    public function destroyDowntimeCategory($id): JsonResponse
    {
        $category = DowntimeCategory::findOrFail($id);
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Kategori Problem berhasil dihapus']);
    }

    public function importDowntimeCategories(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        if (empty($items) || !is_array($items)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data kategori problem untuk diimpor.'], 422);
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($items as $idx => $row) {
            $code = trim($row['code'] ?? $row['Kode Kategori'] ?? $row['KODE'] ?? '');
            $name = trim($row['name'] ?? $row['Nama Kategori'] ?? $row['Kategori Problem'] ?? $row['CATEGORY'] ?? '');
            $typeStr = strtoupper(trim((string) ($row['type'] ?? $row['is_planned'] ?? $row['Tipe'] ?? $row['TYPE'] ?? 'UNPLANNED')));
            $isPlanned = ($typeStr === 'PLANNED' || $typeStr === '1' || $typeStr === 'TRUE');
            $desc = trim($row['description'] ?? $row['Keterangan'] ?? $row['DESCRIPTION'] ?? '');

            if (empty($code) || empty($name)) {
                $errors[] = "Baris #" . ($idx + 1) . ": Kode dan Nama Kategori Problem wajib diisi.";
                continue;
            }

            $cat = DowntimeCategory::where('code', $code)->first();
            if ($cat) {
                $cat->update([
                    'name' => $name,
                    'is_planned' => $isPlanned,
                    'description' => $desc ?: $cat->description,
                ]);
                $updated++;
            } else {
                DowntimeCategory::create([
                    'code' => $code,
                    'name' => $name,
                    'is_planned' => $isPlanned,
                    'description' => $desc,
                ]);
                $imported++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Import Kategori Problem selesai: {$imported} data baru, {$updated} diperbarui.",
            'imported_count' => $imported,
            'updated_count' => $updated,
            'errors' => $errors,
            'data' => DowntimeCategory::orderBy('id')->get()
        ]);
    }

    // DOWNTIME REASONS ALIAS
    public function downtimeReasons(): JsonResponse
    {
        return $this->downtimeCategories();
    }

    public function storeDowntimeReason(Request $request): JsonResponse
    {
        return $this->storeDowntimeCategory($request);
    }

    public function updateDowntimeReason(Request $request, $id): JsonResponse
    {
        return $this->updateDowntimeCategory($request, $id);
    }

    public function destroyDowntimeReason($id): JsonResponse
    {
        return $this->destroyDowntimeCategory($id);
    }

    // NG SECTIONS (BAGIAN NG)
    public function ngSections(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => NgSection::orderBy('id')->get(),
        ]);
    }

    public function storeNgSection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:ng_sections,code|max:50',
            'name' => 'required|string|max:100',
            'component_type' => 'nullable|in:ALL,ASSY,ROD,CAP',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['component_type'] = $validated['component_type'] ?? 'ALL';
        $validated['is_active'] = $request->boolean('is_active', true);
        $section = NgSection::create($validated);

        return response()->json(['success' => true, 'message' => 'Bagian NG berhasil ditambahkan', 'data' => $section], 201);
    }

    public function updateNgSection(Request $request, $id): JsonResponse
    {
        $section = NgSection::findOrFail($id);
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:ng_sections,code,' . $id,
            'name' => 'required|string|max:100',
            'component_type' => 'nullable|in:ALL,ASSY,ROD,CAP',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['component_type'] = $validated['component_type'] ?? $section->component_type ?? 'ALL';
        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $section->update($validated);
        return response()->json(['success' => true, 'message' => 'Bagian NG berhasil diperbarui', 'data' => $section]);
    }

    public function destroyNgSection($id): JsonResponse
    {
        $section = NgSection::findOrFail($id);
        $section->delete();
        return response()->json(['success' => true, 'message' => 'Bagian NG berhasil dihapus']);
    }

    public function importNgSections(Request $request): JsonResponse
    {
        $rows = [];
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $content = file_get_contents($file->getRealPath());
            $rows = $this->parseCsvRows($content);
        } elseif ($request->filled('csv_text')) {
            $rows = $this->parseCsvRows($request->input('csv_text'));
        } elseif (is_array($request->input('rows'))) {
            $rows = $request->input('rows');
        }

        if (empty($rows)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data valid yang ditemukan untuk diimpor.'], 422);
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $normalized = $this->normalizeRowKeys($row);

            $code = $normalized['code'] ?? $normalized['kode'] ?? $normalized['kode_bagian'] ?? $normalized['kode_bagian_ng'] ?? $normalized['kode_ng'] ?? null;
            $name = $normalized['name'] ?? $normalized['nama'] ?? $normalized['nama_bagian'] ?? $normalized['nama_bagian_ng'] ?? $normalized['bagian'] ?? $normalized['area_ng'] ?? null;
            $type = strtoupper($normalized['component_type'] ?? $normalized['tipe'] ?? $normalized['komponen'] ?? $normalized['tipe_komponen'] ?? $normalized['tipe_komponen_all_assy_rod_cap'] ?? $normalized['jenis_komponen'] ?? 'ALL');
            $desc = $normalized['description'] ?? $normalized['deskripsi'] ?? $normalized['deskripsi_area'] ?? $normalized['keterangan'] ?? $normalized['area'] ?? $normalized['penjelasan'] ?? null;
            $isActiveRaw = $normalized['is_active'] ?? $normalized['status'] ?? $normalized['aktif'] ?? $normalized['status_aktif'] ?? $normalized['status_aktif_1_aktif_0_nonaktif'] ?? $normalized['status_aktif_1_ya_0_tidak'] ?? '1';

            if (empty($code) || empty($name)) {
                $errors[] = "Baris #" . ($index + 1) . ": Kode dan Nama Bagian NG wajib diisi.";
                continue;
            }

            if (!in_array($type, ['ALL', 'ASSY', 'ROD', 'CAP'])) {
                $type = 'ALL';
            }

            $isActive = in_array(strtolower((string)$isActiveRaw), ['1', 'true', 'aktif', 'active', 'ya', 'yes', 'y']);

            $existing = NgSection::where('code', $code)->first();
            if ($existing) {
                $existing->update([
                    'name' => $name,
                    'component_type' => $type,
                    'description' => $desc,
                    'is_active' => $isActive,
                ]);
                $updated++;
            } else {
                NgSection::create([
                    'code' => $code,
                    'name' => $name,
                    'component_type' => $type,
                    'description' => $desc,
                    'is_active' => $isActive,
                ]);
                $imported++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk import selesai: {$imported} data baru ditambahkan, {$updated} data diperbarui.",
            'imported_count' => $imported,
            'updated_count' => $updated,
            'errors' => $errors,
            'data' => NgSection::orderBy('id')->get()
        ]);
    }

    // DEFECT CATEGORIES
    public function defectCategories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => DefectCategory::orderBy('id')->get(),
        ]);
    }

    // DEFECT REASONS (PENYEBAB / REMARK)
    public function defectReasons(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => DefectReason::with('defectCategory')->orderBy('id')->get(),
        ]);
    }

    public function storeDefectReason(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'defect_category_id' => 'required|exists:defect_categories,id',
            'code' => 'nullable|string|unique:defect_reasons,code|max:50',
            'name' => 'required|string|max:255',
            'countermeasure' => 'nullable|string',
            'description' => 'nullable|string|max:255',
        ]);

        if (empty($validated['code'])) {
            $cleanSlug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $validated['name']));
            $code = 'DEF-' . (strlen($cleanSlug) > 0 ? substr($cleanSlug, 0, 10) : rand(100, 999));
            $suf = 1;
            $origCode = $code;
            while (DefectReason::where('code', $code)->exists()) {
                $code = substr($origCode, 0, 8) . '-' . $suf++;
            }
            $validated['code'] = $code;
        }

        $reason = DefectReason::create($validated);

        return response()->json(['success' => true, 'message' => 'Penyebab Defect berhasil ditambahkan', 'data' => $reason->load('defectCategory')], 201);
    }

    public function updateDefectReason(Request $request, $id): JsonResponse
    {
        $reason = DefectReason::findOrFail($id);
        $validated = $request->validate([
            'defect_category_id' => 'required|exists:defect_categories,id',
            'code' => 'nullable|string|max:50|unique:defect_reasons,code,' . $id,
            'name' => 'required|string|max:255',
            'countermeasure' => 'nullable|string',
            'description' => 'nullable|string|max:255',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = $reason->code;
        }

        $reason->update($validated);
        return response()->json(['success' => true, 'message' => 'Penyebab Defect berhasil diperbarui', 'data' => $reason->load('defectCategory')]);
    }

    public function updateCountermeasure(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:defect_reasons,id',
            'name' => 'nullable|string',
            'countermeasure' => 'nullable|string',
        ]);

        $reason = null;
        if (!empty($validated['id'])) {
            $reason = DefectReason::find($validated['id']);
        } elseif (!empty($validated['name'])) {
            $reason = DefectReason::where('name', $validated['name'])->first();
            if (!$reason) {
                $cleanSlug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $validated['name']));
                $code = 'DEF-' . (strlen($cleanSlug) > 0 ? substr($cleanSlug, 0, 10) : rand(100, 999));
                $reason = DefectReason::create([
                    'defect_category_id' => 1,
                    'code' => $code,
                    'name' => $validated['name'],
                    'countermeasure' => $validated['countermeasure'],
                ]);
            }
        }

        if ($reason) {
            $reason->update(['countermeasure' => $validated['countermeasure']]);
            return response()->json([
                'success' => true,
                'message' => 'Rekomendasi Tindakan Korektif (Kaizen) berhasil disimpan.',
                'data' => $reason
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Data Defect Reason tidak ditemukan.'], 404);
    }

    public function destroyDefectReason($id): JsonResponse
    {
        $reason = DefectReason::findOrFail($id);
        $reason->delete();
        return response()->json(['success' => true, 'message' => 'Penyebab Defect berhasil dihapus']);
    }

    public function importDefectReasons(Request $request): JsonResponse
    {
        $rows = [];
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $content = file_get_contents($file->getRealPath());
            $rows = $this->parseCsvRows($content);
        } elseif ($request->filled('csv_text')) {
            $rows = $this->parseCsvRows($request->input('csv_text'));
        } elseif (is_array($request->input('rows'))) {
            $rows = $request->input('rows');
        }

        if (empty($rows)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data valid yang ditemukan untuk diimpor.'], 422);
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        // Preload categories
        $categories = DefectCategory::all();

        foreach ($rows as $index => $row) {
            $normalized = $this->normalizeRowKeys($row);

            $name = $normalized['name'] ?? $normalized['nama'] ?? $normalized['nama_penyebab'] ?? $normalized['nama_penyebab_remark'] ?? $normalized['penyebab'] ?? $normalized['remark'] ?? $normalized['nama_defect'] ?? $normalized['nama_cacat'] ?? null;
            $catRaw = $normalized['category'] ?? $normalized['kategori'] ?? $normalized['kategori_defect'] ?? $normalized['kategori_penyebab'] ?? $normalized['defect_category'] ?? $normalized['kategori_cacat'] ?? $normalized['defect_category_id'] ?? null;
            $code = $normalized['code'] ?? $normalized['kode'] ?? $normalized['kode_penyebab'] ?? $normalized['kode_defect'] ?? $normalized['kode_cacat'] ?? null;
            $countermeasure = $normalized['countermeasure'] ?? $normalized['rekomendasi_kaizen'] ?? $normalized['rekomendasi'] ?? $normalized['tindakan_korektif'] ?? $normalized['kaizen'] ?? $normalized['solusi'] ?? null;
            $desc = $normalized['description'] ?? $normalized['deskripsi'] ?? $normalized['deskripsi_penjelasan'] ?? $normalized['deskripsi_area'] ?? $normalized['keterangan'] ?? $normalized['penjelasan'] ?? null;

            if (empty($name)) {
                $errors[] = "Baris #" . ($index + 1) . ": Nama Penyebab / Remark wajib diisi.";
                continue;
            }

            // Resolve Category ID
            $categoryId = 1;
            if (!empty($catRaw)) {
                if (is_numeric($catRaw)) {
                    $cat = $categories->firstWhere('id', (int)$catRaw);
                    if ($cat) {
                        $categoryId = $cat->id;
                    }
                } else {
                    $catRawTrim = trim($catRaw);
                    $cat = $categories->first(function ($c) use ($catRawTrim) {
                        return strcasecmp($c->name, $catRawTrim) === 0 
                            || strcasecmp($c->code ?? '', $catRawTrim) === 0
                            || stripos($c->name, $catRawTrim) !== false
                            || stripos($catRawTrim, $c->name) !== false;
                    });
                    if (!$cat) {
                        $cleanSlug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $catRawTrim));
                        $catCode = 'CAT-' . (strlen($cleanSlug) > 0 ? substr($cleanSlug, 0, 12) : rand(100, 999));
                        
                        // Ensure unique code
                        $suffix = 1;
                        $origCode = $catCode;
                        while (DefectCategory::where('code', $catCode)->exists()) {
                            $catCode = substr($origCode, 0, 10) . '-' . $suffix++;
                        }

                        $cat = DefectCategory::create([
                            'code' => $catCode,
                            'name' => $catRawTrim,
                        ]);
                        $categories->push($cat);
                    }
                    $categoryId = $cat->id;
                }
            }

            // If code is empty, generate one from the name
            if (empty($code)) {
                $cleanSlug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $name));
                $code = 'DEF-' . (strlen($cleanSlug) > 0 ? substr($cleanSlug, 0, 10) : rand(100, 999));
            }

            // Find existing by code or by (name + category)
            $existing = DefectReason::where('code', $code)
                ->orWhere(function ($q) use ($name, $categoryId) {
                    $q->where('name', $name)->where('defect_category_id', $categoryId);
                })->first();

            if ($existing) {
                $updateData = [
                    'defect_category_id' => $categoryId,
                    'name' => $name,
                    'description' => $desc,
                ];
                if (!empty($countermeasure)) {
                    $updateData['countermeasure'] = $countermeasure;
                }
                $existing->update($updateData);
                $updated++;
            } else {
                // Ensure unique code before creating
                $suffix = 1;
                $origCode = $code;
                while (DefectReason::where('code', $code)->exists()) {
                    $code = substr($origCode, 0, 8) . '-' . $suffix++;
                }

                DefectReason::create([
                    'defect_category_id' => $categoryId,
                    'code' => $code,
                    'name' => $name,
                    'countermeasure' => $countermeasure,
                    'description' => $desc,
                ]);
                $imported++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk import selesai: {$imported} data baru ditambahkan, {$updated} data diperbarui.",
            'imported_count' => $imported,
            'updated_count' => $updated,
            'errors' => $errors,
            'data' => DefectReason::with('defectCategory')->orderBy('id')->get()
        ]);
    }

    /**
     * Helper to parse CSV string into associative array
     */
    private function parseCsvRows(string $content): array
    {
        // Strip UTF-8 BOM if present
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^{$bom}/", '', $content);

        $lines = preg_split("/\r\n|\n|\r/", trim($content));
        if (empty($lines)) return [];

        // Detect delimiter (, or ;)
        $firstLine = $lines[0];
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $header = str_getcsv(array_shift($lines), $delimiter);
        $header = array_map(function($h) {
            $h = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $h), '_'));
            return preg_replace('/_+/', '_', $h);
        }, $header);

        $rows = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $values = str_getcsv($line, $delimiter);
            if (count($values) === count($header)) {
                $rows[] = array_combine($header, array_map('trim', $values));
            } elseif (count($values) > 0) {
                $row = [];
                foreach ($header as $i => $colName) {
                    $row[$colName] = isset($values[$i]) ? trim($values[$i]) : '';
                }
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Helper to normalize array keys
     */
    private function normalizeRowKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $k => $v) {
            $key = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', (string)$k), '_'));
            $key = preg_replace('/_+/', '_', $key);
            $normalized[$key] = is_string($v) ? trim($v) : $v;
        }
        return $normalized;
    }

    // SHIFTS
    public function shifts(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Shift::all(),
        ]);
    }

    public function storeShift(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'break_duration_minutes' => 'required|integer|min:0',
        ]);

        $validated['plant_id'] = $request->input('plant_id', 1);
        $shift = Shift::create($validated);

        return response()->json(['success' => true, 'message' => 'Shift parameter created successfully', 'data' => $shift], 201);
    }

    public function updateShift(Request $request, $id): JsonResponse
    {
        $shift = Shift::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'break_duration_minutes' => 'required|integer|min:0',
        ]);

        $shift->update($validated);
        return response()->json(['success' => true, 'message' => 'Shift parameter updated successfully', 'data' => $shift]);
    }

    public function destroyShift($id): JsonResponse
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();
        return response()->json(['success' => true, 'message' => 'Shift deleted successfully']);
    }

    // SYSTEM SETTINGS
    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SystemSetting::all(),
        ]);
    }

    public function updateSetting(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);

        $setting = SystemSetting::set($request->key, $request->value);

        return response()->json([
            'success' => true,
            'message' => 'System setting updated successfully.',
            'data' => $setting,
        ]);
    }

    // COMPANY PROFILE & LETTERHEAD SETTINGS
    public function getCompanyProfile(): JsonResponse
    {
        $profileRaw = SystemSetting::get('company_profile');
        $defaultProfile = [
            'company_name' => 'PT. YASUNAGA INDONESIA',
            'company_tagline' => 'Engine Parts & Air Pump Manufacturing',
            'plant_name' => 'ENGINE PARTS & AIR PUMP MFG',
            'plant_code' => 'PLT-01',
            'plant_location' => 'Kawasan Industri Modern Cikande, Serang - Banten',
            'address' => 'Jl. Modern Industri Raya Kav. 24 Kawasan Industri Modern Cikande, Nambo Ilir Kibin Serang Banten',
            'phone' => '(0254) 400306',
            'email' => 'prodcr@yasunaga.co.id',
            'website' => 'www.yasunaga.co.jp',
            'doc_prefix' => 'YSN-OEE',
            'app_title' => 'LinePulse | Smart Production Performance Monitoring',
            'company_logo' => '/images/yasunaga-logo.png',
            'letterhead_enabled' => true,
        ];

        if ($profileRaw) {
            $parsed = is_array($profileRaw) ? $profileRaw : json_decode($profileRaw, true);
            $profile = array_merge($defaultProfile, is_array($parsed) ? $parsed : []);
        } else {
            $profile = $defaultProfile;
        }

        if (empty($profile['company_logo'])) {
            $profile['company_logo'] = '/images/yasunaga-logo.png';
        }

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    public function saveCompanyProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_tagline' => 'nullable|string|max:255',
            'plant_name' => 'required|string|max:255',
            'plant_code' => 'required|string|max:50',
            'plant_location' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|string|max:150',
            'website' => 'nullable|string|max:150',
            'doc_prefix' => 'nullable|string|max:50',
            'app_title' => 'nullable|string|max:255',
            'company_logo' => 'nullable|string', // Base64 data string or URL
            'letterhead_enabled' => 'nullable|boolean',
        ]);

        SystemSetting::set('company_profile', $validated, 'company', 'Profil Perusahaan, Logo, dan Kop Surat Laporan Resmi');

        // Also update individual related settings for backward compatibility
        SystemSetting::set('company_name', $validated['company_name']);
        SystemSetting::set('plant_name', $validated['plant_name']);
        SystemSetting::set('app_title', $validated['app_title'] ?? 'OEE Sys');

        return response()->json([
            'success' => true,
            'message' => 'Profil Perusahaan dan Kop Laporan berhasil disimpan.',
            'data' => $validated,
        ]);
    }

    // GROUPS (TEAM LEADERS)
    public function groups(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Group::with('productionLine')->get(),
        ]);
    }

    public function storeGroup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team' => 'required|string',
            'leader_name' => 'required|string|max:255',
            'supervisor_name' => 'nullable|string|max:255',
            'production_line_id' => 'required|exists:production_lines,id',
        ]);

        $group = \App\Models\Group::updateOrCreate(
            [
                'production_line_id' => $validated['production_line_id'],
                'team' => $validated['team'],
            ],
            [
                'leader_name' => $validated['leader_name'],
                'supervisor_name' => $validated['supervisor_name'] ?? 'Alim Utama',
            ]
        );

        return response()->json(['success' => true, 'message' => 'Team Leader Group saved successfully', 'data' => $group->load('productionLine')], 201);
    }

    public function updateGroup(Request $request, $id): JsonResponse
    {
        $group = \App\Models\Group::findOrFail($id);
        $validated = $request->validate([
            'team' => 'required|string',
            'leader_name' => 'required|string|max:255',
            'supervisor_name' => 'nullable|string|max:255',
            'production_line_id' => 'required|exists:production_lines,id',
        ]);

        $group->update($validated);
        return response()->json(['success' => true, 'message' => 'Team Leader Group updated successfully', 'data' => $group->load('productionLine')]);
    }

    public function destroyGroup($id): JsonResponse
    {
        $group = \App\Models\Group::findOrFail($id);
        $group->delete();
        return response()->json(['success' => true, 'message' => 'Team Leader Group deleted successfully']);
    }
}
