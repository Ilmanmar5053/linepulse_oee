<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditTrailObserver
{
    /**
     * Map model class names to friendly module names and descriptors
     */
    private function getModelMeta(Model $model): array
    {
        $class = class_basename($model);
        
        switch ($class) {
            case 'ProductionRecord':
                $line = $model->productionLine?->name ?? ($model->production_line_id ? "Line #{$model->production_line_id}" : '');
                $shift = $model->shift?->name ?? ($model->shift_id ? "Shift {$model->shift_id}" : '');
                $prod = $model->product?->name ?? ($model->product_id ? "Produk #{$model->product_id}" : '');
                $date = $model->production_date ? " Tgl {$model->production_date}" : '';
                return [
                    'module' => 'Produksi & OEE',
                    'entity' => 'Laporan Produksi',
                    'detail' => trim("{$line} {$shift}{$date} ({$prod})"),
                    'severity_create' => 'info',
                    'severity_update' => 'info',
                    'severity_delete' => 'danger',
                ];

            case 'Downtime':
                $line = $model->productionLine?->name ?? ($model->production_line_id ? "Line #{$model->production_line_id}" : '');
                $machine = $model->machine?->name ?? ($model->machine_id ? "Mesin #{$model->machine_id}" : '');
                $reason = $model->reason ?? $model->problem ?? 'Downtime Stop';
                $mins = $model->calculated_duration_minutes ?? $model->duration_minutes ?? 0;
                return [
                    'module' => 'Downtime Mesin',
                    'entity' => 'Problem & Downtime',
                    'detail' => trim("{$line} / {$machine} - {$reason} ({$mins} Menit)"),
                    'severity_create' => 'info',
                    'severity_update' => 'warning',
                    'severity_delete' => 'danger',
                ];

            case 'NgRecord':
                $line = $model->productionRecord?->productionLine?->name ?? '';
                $type = $model->defect_type ?? $model->ng_type ?? 'Defect';
                $qty = $model->quantity ?? $model->total_ng_qty ?? 0;
                return [
                    'module' => 'Detail Reject NG',
                    'entity' => 'Catatan Reject/Defect',
                    'detail' => trim("{$line} - {$type} (Qty: {$qty} pcs)"),
                    'severity_create' => 'info',
                    'severity_update' => 'info',
                    'severity_delete' => 'danger',
                ];

            case 'ProductionLine':
                $name = $model->name ?? $model->code ?? "ID #{$model->id}";
                return [
                    'module' => 'Master Data',
                    'entity' => 'Lini Produksi',
                    'detail' => "Line {$name}",
                    'severity_create' => 'info',
                    'severity_update' => 'warning',
                    'severity_delete' => 'danger',
                ];

            case 'Machine':
                $name = $model->name ?? $model->code ?? "ID #{$model->id}";
                return [
                    'module' => 'Master Data',
                    'entity' => 'Mesin Produksi',
                    'detail' => "Mesin {$name}",
                    'severity_create' => 'info',
                    'severity_update' => 'warning',
                    'severity_delete' => 'danger',
                ];

            case 'Product':
                $name = $model->name ?? $model->code ?? $model->part_number ?? "ID #{$model->id}";
                return [
                    'module' => 'Master Data',
                    'entity' => 'Master Produk / SKU',
                    'detail' => "Part {$name}",
                    'severity_create' => 'info',
                    'severity_update' => 'warning',
                    'severity_delete' => 'danger',
                ];

            case 'ProductCycleTime':
                $line = $model->productionLine?->name ?? '';
                $prod = $model->product?->name ?? '';
                $ct = $model->cycle_time_seconds ?? 0;
                return [
                    'module' => 'Master Data',
                    'entity' => 'Cycle Time & Target Line',
                    'detail' => "{$line} - {$prod} (CT: {$ct}s)",
                    'severity_create' => 'info',
                    'severity_update' => 'warning',
                    'severity_delete' => 'danger',
                ];

            case 'Shift':
                $name = $model->name ?? "Shift #{$model->id}";
                return [
                    'module' => 'Master Data',
                    'entity' => 'Shift Kerja',
                    'detail' => "{$name}",
                    'severity_create' => 'info',
                    'severity_update' => 'warning',
                    'severity_delete' => 'danger',
                ];

            case 'DefectCategory':
            case 'DefectReason':
                $name = $model->name ?? $model->code ?? "ID #{$model->id}";
                return [
                    'module' => 'Master Data',
                    'entity' => 'Master Defect / NG',
                    'detail' => "{$name}",
                    'severity_create' => 'info',
                    'severity_update' => 'warning',
                    'severity_delete' => 'danger',
                ];

            case 'DowntimeCategory':
            case 'DowntimeReason':
                $name = $model->name ?? $model->code ?? "ID #{$model->id}";
                return [
                    'module' => 'Master Data',
                    'entity' => 'Master Kategori Downtime',
                    'detail' => "{$name}",
                    'severity_create' => 'info',
                    'severity_update' => 'warning',
                    'severity_delete' => 'danger',
                ];

            case 'SystemSetting':
                $key = $model->key ?? $model->setting_key ?? "Config #{$model->id}";
                return [
                    'module' => 'Pengaturan Sistem',
                    'entity' => 'Konfigurasi Parameter',
                    'detail' => "{$key}",
                    'severity_create' => 'warning',
                    'severity_update' => 'warning',
                    'severity_delete' => 'critical',
                ];

            case 'User':
                $name = $model->name ?? $model->email ?? "User #{$model->id}";
                return [
                    'module' => 'Manajemen Pengguna',
                    'entity' => 'Akun Pengguna',
                    'detail' => "{$name}",
                    'severity_create' => 'warning',
                    'severity_update' => 'warning',
                    'severity_delete' => 'critical',
                ];

            default:
                return [
                    'module' => 'Operasional Sistem',
                    'entity' => $class,
                    'detail' => "ID #{$model->getKey()}",
                    'severity_create' => 'info',
                    'severity_update' => 'info',
                    'severity_delete' => 'danger',
                ];
        }
    }

    public function created(Model $model): void
    {
        if ($model instanceof AuditLog) return;

        $meta = $this->getModelMeta($model);
        $desc = "Menambahkan data {$meta['entity']}: {$meta['detail']}";

        AuditLog::record(
            action: 'CREATE',
            module: $meta['module'],
            description: $desc,
            oldValue: null,
            newValue: $model->getAttributes(),
            recordId: $model->getKey(),
            severity: $meta['severity_create'],
            status: 'SUCCESS'
        );
    }

    public function updated(Model $model): void
    {
        if ($model instanceof AuditLog) return;

        // Skip if no actual changes
        $changes = $model->getChanges();
        unset($changes['updated_at']);
        if (empty($changes)) return;

        $meta = $this->getModelMeta($model);
        
        $changedFields = array_keys($changes);
        $fieldSummary = implode(', ', array_slice($changedFields, 0, 4));
        if (count($changedFields) > 4) {
            $fieldSummary .= ' +' . (count($changedFields) - 4) . ' kolom lainnya';
        }

        $desc = "Memperbarui {$meta['entity']} ({$meta['detail']}) pada kolom: [{$fieldSummary}]";

        // Old attributes for changed fields
        $original = $model->getOriginal();

        AuditLog::record(
            action: 'UPDATE',
            module: $meta['module'],
            description: $desc,
            oldValue: $original,
            newValue: $model->getAttributes(),
            recordId: $model->getKey(),
            severity: $meta['severity_update'],
            status: 'SUCCESS'
        );
    }

    public function deleted(Model $model): void
    {
        if ($model instanceof AuditLog) return;

        $meta = $this->getModelMeta($model);
        $desc = "Menghapus data {$meta['entity']}: {$meta['detail']}";

        AuditLog::record(
            action: 'DELETE',
            module: $meta['module'],
            description: $desc,
            oldValue: $model->getOriginal(),
            newValue: null,
            recordId: $model->getKey(),
            severity: $meta['severity_delete'],
            status: 'SUCCESS'
        );
    }
}
