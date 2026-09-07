<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseManagementController extends Controller
{
    /**
     * Whitelist of transactional tables that can be cleaned for Go-Live.
     * Master data and configuration tables are STRICTLY EXCLUDED.
     */
    protected array $transactionalTables = [
        'production_records' => [
            'label' => 'Data Laporan Hasil Produksi',
            'desc' => 'Catatan hasil output mesin, target kuantitas, reject count, dan cycle time.',
            'category' => 'production'
        ],
        'oee_records' => [
            'label' => 'Record Kalkulasi OEE (A/P/Q)',
            'desc' => 'Data skor Availability, Performance, Quality, dan Total OEE harian.',
            'category' => 'production'
        ],
        'downtimes' => [
            'label' => 'Log Trouble & Downtime Mesin',
            'desc' => 'Riwayat kendala operasional, breakdown, perbaikan teknisi, dan CAPA.',
            'category' => 'downtime'
        ],
        'ng_records' => [
            'label' => 'Data Rekap Defect / NG Queue',
            'desc' => 'Daftar rekap pemeriksaan mutu reject part dari lini per shift.',
            'category' => 'ng_quality'
        ],
        'ng_record_items' => [
            'label' => 'Rincian Item Defect NG',
            'desc' => 'Detail jenis defect dan jumlah part reject per seksi komponen.',
            'category' => 'ng_quality'
        ],
        'quality_records' => [
            'label' => 'Log Pemeriksaan Mutu (QC Logs)',
            'desc' => 'Riwayat inspeksi sampling mutu dan verifikasi reject rate.',
            'category' => 'ng_quality'
        ],
        'daily_production_summaries' => [
            'label' => 'Ringkasan Produksi Harian',
            'desc' => 'Snapshot ringkasan harian untuk pelaporan cepat.',
            'category' => 'production'
        ],
        'shift_summaries' => [
            'label' => 'Rekapitulasi Shift Handover',
            'desc' => 'Log akumulasi performa antar shift produksi.',
            'category' => 'production'
        ],
        'machine_events' => [
            'label' => 'Log Event Sensor Mesin',
            'desc' => 'Pencatatan sinyal start/stop dan perubahan status IoT.',
            'category' => 'monitoring'
        ],
        'machine_status_logs' => [
            'label' => 'Riwayat Status Mesin',
            'desc' => 'Log perubahan status Running, Idle, dan Maintenance.',
            'category' => 'monitoring'
        ],
        'audit_logs' => [
            'label' => 'Log Audit Aktivitas Uji Coba',
            'desc' => 'Riwayat aktivitas user selama masa pengujian sistem.',
            'category' => 'monitoring'
        ],
    ];

    /**
     * Master & Configuration tables that are PROTECTED by safety shield.
     */
    protected array $protectedMasterTables = [
        'users' => 'Akun Pengguna & Profil',
        'roles' => 'Role Akses Pengguna',
        'permissions' => 'Izin Akses Fitur',
        'permission_role' => 'Pemetaan Role-Izin',
        'role_user' => 'Penugasan Role User',
        'system_settings' => 'Pengaturan Target OEE & Standar',
        'plants' => 'Master Pabrik / Site',
        'production_lines' => 'Master Lini Produksi',
        'machines' => 'Master Mesin Operasional',
        'products' => 'Master Part Number & Produk',
        'product_categories' => 'Kategori Part / Produk',
        'shifts' => 'Master Jam Kerja Shift',
        'groups' => 'Master Regu & Leader PIC',
        'downtime_categories' => 'Kategori Trouble Downtime',
        'downtime_reasons' => 'Master Alasan Downtime Mesin',
        'ng_sections' => 'Master Seksi Defect NG',
        'defect_categories' => 'Kategori Defect Mutu',
        'defect_reasons' => 'Master Jenis Defect Part',
        'departments' => 'Master Departemen Pabrik',
        'areas' => 'Master Area / Blok Kerja',
        'work_centers' => 'Master Work Center',
        'machine_types' => 'Master Jenis / Tipe Mesin',
        'operators' => 'Master Data Operator',
        'employees' => 'Master Data Karyawan',
        'company_profiles' => 'Profil Perusahaan & Kop Surat',
    ];

    /**
     * Get Database & Environment technical diagnostics and table statistics.
     */
    public function getDatabaseInfo(): JsonResponse
    {
        try {
            $dbConnection = config('database.default', 'mysql');
            $dbConfig = config("database.connections.{$dbConnection}", []);
            $dbName = $dbConfig['database'] ?? 'oee_sys';
            $dbHost = $dbConfig['host'] ?? '127.0.0.1';
            $dbPort = $dbConfig['port'] ?? '3306';
            $dbDriver = $dbConfig['driver'] ?? 'mysql';

            // Server Version & DB Size
            $version = 'Unknown';
            $totalSizeMb = 0.0;
            $tableSizes = [];

            try {
                $verRow = DB::select('SELECT VERSION() as ver');
                if (!empty($verRow)) {
                    $version = $verRow[0]->ver;
                }

                $sizeQuery = DB::select("
                    SELECT 
                        table_name AS `table`,
                        ROUND(((data_length + index_length) / 1024), 2) AS `size_kb`,
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS `size_mb`,
                        table_rows AS `rows_approx`
                    FROM information_schema.TABLES
                    WHERE table_schema = ?
                ", [$dbName]);

                foreach ($sizeQuery as $row) {
                    $tableSizes[$row->table] = [
                        'size_kb' => (float) $row->size_kb,
                        'size_mb' => (float) $row->size_mb,
                    ];
                    $totalSizeMb += (float) $row->size_mb;
                }
            } catch (\Exception $e) {
                // Fallback for non-MySQL or permission limits
            }

            // Tables breakdown
            $transactionalStats = [];
            $totalTransactionRows = 0;

            foreach ($this->transactionalTables as $tableName => $info) {
                if (Schema::hasTable($tableName)) {
                    $rowCount = DB::table($tableName)->count();
                    $totalTransactionRows += $rowCount;
                    $sizeInfo = $tableSizes[$tableName] ?? ['size_kb' => 0, 'size_mb' => 0];

                    $transactionalStats[] = [
                        'table' => $tableName,
                        'label' => $info['label'],
                        'description' => $info['desc'],
                        'category' => $info['category'],
                        'count' => $rowCount,
                        'size_kb' => $sizeInfo['size_kb'],
                        'can_clean' => true,
                    ];
                }
            }

            $masterStats = [];
            $totalMasterRows = 0;

            foreach ($this->protectedMasterTables as $tableName => $label) {
                if (Schema::hasTable($tableName)) {
                    $rowCount = DB::table($tableName)->count();
                    $totalMasterRows += $rowCount;
                    $sizeInfo = $tableSizes[$tableName] ?? ['size_kb' => 0, 'size_mb' => 0];

                    $masterStats[] = [
                        'table' => $tableName,
                        'label' => $label,
                        'count' => $rowCount,
                        'size_kb' => $sizeInfo['size_kb'],
                        'is_protected' => true,
                    ];
                }
            }

            $charsetCollation = ($dbConfig['charset'] ?? 'utf8mb4') . ' / ' . ($dbConfig['collation'] ?? 'utf8mb4_unicode_ci');

            return response()->json([
                'success' => true,
                'data' => [
                    'database' => [
                        'connection' => $dbConnection,
                        'database_name' => $dbName,
                        'host' => $dbHost,
                        'port' => $dbPort,
                        'driver' => $dbDriver,
                        'server_version' => $version,
                        'charset_collation' => $charsetCollation,
                        'total_size_mb' => round($totalSizeMb, 2),
                        'total_transaction_rows' => $totalTransactionRows,
                        'total_master_rows' => $totalMasterRows,
                    ],
                    'environment' => [
                        'php_version' => PHP_VERSION,
                        'laravel_version' => app()->version(),
                        'app_env' => config('app.env', 'production'),
                        'app_debug' => config('app.debug', false),
                        'app_url' => config('app.url', 'http://localhost'),
                        'app_name' => config('app.name', 'OEE Performance System'),
                        'timezone' => config('app.timezone', 'Asia/Jakarta'),
                        'server_os' => PHP_OS_FAMILY . ' (' . php_uname('s') . ')',
                        'session_driver' => config('session.driver', 'database'),
                        'cache_driver' => config('cache.default', 'database'),
                        'queue_driver' => config('queue.default', 'database'),
                        'current_timestamp' => Carbon::now()->format('Y-m-d H:i:s T'),
                    ],
                    'tables' => [
                        'transactional' => $transactionalStats,
                        'master' => $masterStats,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil informasi database: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Safely clean transactional data for Go-Live.
     * Guaranteed never to delete master data or configurations.
     */
    public function cleanTransactions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'scope' => 'required|string|in:all_transactions,production,downtime,ng_quality,monitoring',
            'confirmation_phrase' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $phrase = trim(strtoupper($request->input('confirmation_phrase')));
        if ($phrase !== 'BERSIHKAN' && $phrase !== 'CONFIRM') {
            return response()->json([
                'success' => false,
                'message' => 'Frasa konfirmasi tidak sesuai. Harap ketik "BERSIHKAN" untuk melanjutkan tindakan pembersihan.',
            ], 422);
        }

        $scope = $request->input('scope');
        $tablesToClean = [];

        foreach ($this->transactionalTables as $tableName => $info) {
            if ($scope === 'all_transactions' || $info['category'] === $scope) {
                if (Schema::hasTable($tableName)) {
                    $tablesToClean[] = $tableName;
                }
            }
        }

        if (empty($tablesToClean)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada tabel transaksi yang terpilih untuk dibersihkan.',
            ], 400);
        }

        // HARDCODED SAFETY GUARD: Ensure no master table can ever be in $tablesToClean
        foreach ($tablesToClean as $targetTable) {
            if (array_key_exists($targetTable, $this->protectedMasterTables)) {
                return response()->json([
                    'success' => false,
                    'message' => "Pembersihan dibatalkan: Tabel master '{$targetTable}' terproteksi dan tidak boleh dihapus.",
                ], 403);
            }
        }

        $cleanedStats = [];
        $totalDeletedRows = 0;

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tablesToClean as $table) {
                $countBefore = DB::table($table)->count();
                DB::table($table)->truncate();
                $cleanedStats[] = [
                    'table' => $table,
                    'deleted_rows' => $countBefore,
                ];
                $totalDeletedRows += $countBefore;
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json([
                'success' => true,
                'message' => "Pembersihan data transaksional berhasil! Total {$totalDeletedRows} baris data uji coba telah dibersihkan.",
                'data' => [
                    'scope' => $scope,
                    'cleaned_tables' => $cleanedStats,
                    'total_deleted_rows' => $totalDeletedRows,
                    'cleaned_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan pembersihan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export / Download snapshot backup of transactional data as JSON.
     */
    public function exportBackup(Request $request): StreamedResponse|JsonResponse
    {
        try {
            $backupData = [
                'metadata' => [
                    'app_name' => config('app.name', 'OEE Performance System'),
                    'backup_type' => 'Transactional Trial Data Backup',
                    'exported_at' => Carbon::now()->toIso8601String(),
                    'app_version' => '1.0.0-PROD',
                    'database_name' => config('database.connections.mysql.database', 'oee_sys'),
                ],
                'tables' => [],
            ];

            foreach (array_keys($this->transactionalTables) as $table) {
                if (Schema::hasTable($table)) {
                    $backupData['tables'][$table] = DB::table($table)->get()->toArray();
                }
            }

            $fileName = 'Backup_OEE_Yasunaga_' . Carbon::now()->format('Y-m-d_His') . '.json';
            $jsonContent = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            return response()->streamDownload(function () use ($jsonContent) {
                echo $jsonContent;
            }, $fileName, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat file backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run table optimization to defragment indexes and reclaim disk space.
     */
    public function optimizeDatabase(): JsonResponse
    {
        try {
            $allTables = array_merge(
                array_keys($this->transactionalTables),
                array_keys($this->protectedMasterTables)
            );

            $optimized = [];
            foreach ($allTables as $table) {
                if (Schema::hasTable($table)) {
                    try {
                        DB::statement("OPTIMIZE TABLE `{$table}`");
                        $optimized[] = $table;
                    } catch (\Exception $ex) {
                        // Some tables may not support optimize (e.g. Memory engine), continue
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Optimasi dan defragmentasi tabel database berhasil dijalankan.',
                'data' => [
                    'optimized_tables_count' => count($optimized),
                    'optimized_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengoptimasi database: ' . $e->getMessage(),
            ], 500);
        }
    }
}
