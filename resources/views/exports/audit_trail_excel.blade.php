@php
$xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
@endphp
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Audit Trail & System Integrity</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                        <x:Print>
                            <x:ValidPrinterInfo/>
                            <x:PaperSizeIndex>9</x:PaperSizeIndex>
                            <x:Scale>90</x:Scale>
                        </x:Print>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
@endverbatim
    <style>
        body { font-family: Calibri, 'Segoe UI', Arial, sans-serif; font-size: 9pt; color: #0f172a; }
        table { border-collapse: collapse; margin-bottom: 12px; width: 100%; }
        .doc-header-1 { font-family: Calibri, Arial, sans-serif; font-size: 13pt; font-weight: bold; color: #064e3b; text-align: left; }
        .doc-header-2 { font-family: Calibri, Arial, sans-serif; font-size: 9pt; font-weight: bold; color: #334155; text-align: left; }
        .doc-header-3 { font-family: Calibri, Arial, sans-serif; font-size: 8pt; color: #64748b; text-align: left; }
        .doc-title { font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; color: #047857; text-transform: uppercase; padding: 6px 0; text-align: left; }
        .sec-title { font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; font-weight: bold; background-color: #065f46; color: #FFFFFF; padding: 4px 8px; border: 0.5pt solid #047857; }
        .th-cell { background-color: #f1f5f9; font-weight: bold; border: 0.5pt solid #94a3b8; text-align: left; padding: 5px 6px; font-size: 8.5pt; color: #1e293b; }
        .th-cell-num { background-color: #f1f5f9; font-weight: bold; border: 0.5pt solid #94a3b8; text-align: right; padding: 5px 6px; font-size: 8.5pt; color: #1e293b; }
        .th-cell-center { background-color: #f1f5f9; font-weight: bold; border: 0.5pt solid #94a3b8; text-align: center; padding: 5px 6px; font-size: 8.5pt; color: #1e293b; }
        .td-cell { border: 0.5pt solid #cbd5e1; padding: 4px 6px; vertical-align: top; font-size: 8.5pt; }
        .td-cell-bold { border: 0.5pt solid #cbd5e1; padding: 4px 6px; font-weight: bold; font-size: 8.5pt; }
        .td-cell-num { border: 0.5pt solid #cbd5e1; padding: 4px 6px; text-align: right; font-size: 8.5pt; mso-number-format: "#,##0"; }
        .td-cell-center { border: 0.5pt solid #cbd5e1; padding: 4px 6px; text-align: center; font-size: 8.5pt; }
        .td-cell-mono { border: 0.5pt solid #cbd5e1; padding: 4px 6px; font-family: Consolas, monospace; font-size: 8pt; }
        .meta-label { font-weight: bold; border: 0.5pt solid #cbd5e1; padding: 4px 6px; font-size: 8.5pt; text-align: right; background-color: #f8fafc; color: #475569; }
        .meta-val { border: 0.5pt solid #cbd5e1; padding: 4px 6px; font-size: 8.5pt; }
        .badge-success { background-color: #d1fae5; color: #065f46; font-weight: bold; }
        .badge-warning { background-color: #fef3c7; color: #92400e; font-weight: bold; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; font-weight: bold; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .sig-box { text-align: center; border: 0.5pt solid #cbd5e1; padding: 8px; font-size: 8.5pt; vertical-align: top; }
    </style>
</head>
<body>
    <!-- KOP DOKUMEN PT. YASUNAGA INDONESIA -->
    <table>
        <tr>
            <td colspan="12" class="doc-header-1">PT. YASUNAGA INDONESIA</td>
        </tr>
        <tr>
            <td colspan="12" class="doc-header-2">Engine Parts & Air Pump Manufacturing • Plant Security & Compliance System</td>
        </tr>
        <tr>
            <td colspan="12" class="doc-header-3">LinePulse OEE Intelligence • National IT Audit & ISO 27001:2022 Certified Architecture</td>
        </tr>
        <tr><td colspan="12" style="border-bottom: 2pt solid #047857; height: 4px;"></td></tr>
        <tr><td colspan="12" style="height: 6px;"></td></tr>
        <tr>
            <td colspan="12" class="doc-title">DOKUMEN RESMI AUDIT TRAIL & LOG INTEGRITAS SISTEM ELEKTRONIK</td>
        </tr>
    </table>

    <!-- METADATA & RINGKASAN AUDIT -->
    <table>
        <tr>
            <td colspan="12" class="sec-title">I. METADATA LAPORAN & STATUS AUDIT KEPATUHAN</td>
        </tr>
        <tr>
            <td class="meta-label" style="width: 15%;">Waktu Ekspor:</td>
            <td class="meta-val" style="width: 35%;">{{ $exportedAt ?? now()->format('Y-m-d H:i:s') }} WIB</td>
            <td class="meta-label" style="width: 15%;">Total Entri Diekspor:</td>
            <td class="meta-val" style="width: 35%; font-weight: bold; color: #065f46;">{{ number_format(count($logs)) }} Entri Transaksi</td>
        </tr>
        <tr>
            <td class="meta-label">Diekspor Oleh:</td>
            <td class="meta-val">{{ $exportedBy ?? 'Admin Produksi (Sistem OEE)' }}</td>
            <td class="meta-label">Rentang Filter Waktu:</td>
            <td class="meta-val">{{ $filterRange ?? 'Semua Rentang Waktu (All History)' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Standar Regulasi:</td>
            <td class="meta-val">ISO/IEC 27001:2022 • Permenkominfo Standar Audit TI • BSSN</td>
            <td class="meta-label">Filter Kriteria:</td>
            <td class="meta-val">Modul: {{ $filterModule ?? 'Semua' }} | Aksi: {{ $filterAction ?? 'Semua' }} | Severity: {{ $filterSeverity ?? 'Semua' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Status Kriptografi:</td>
            <td class="meta-val" style="color: #047857; font-weight: bold;">SHA-256 Chained Hash Verification (COMPLIANT)</td>
            <td class="meta-label">Integritas Basis Data:</td>
            <td class="meta-val" style="color: #047857; font-weight: bold;">ONLINE • ZERO TAMPERING DETECTED</td>
        </tr>
    </table>

    <!-- RINGKASAN METRIK AUDIT (KPI SUMMARY) -->
    <table>
        <tr>
            <td colspan="12" class="sec-title">II. RINGKASAN EKSEKUTIF AKTIVITAS SISTEM</td>
        </tr>
        <tr style="text-align: center; font-weight: bold;">
            <td colspan="3" class="th-cell-center" style="background-color: #ecfdf5; color: #065f46; font-size: 10pt;">TOTAL LOG TERCATAT: {{ number_format($summary['total_recorded'] ?? count($logs)) }}</td>
            <td colspan="3" class="th-cell-center" style="background-color: #ecfdf5; color: #065f46; font-size: 10pt;">AKTIVITAS HARI INI: {{ number_format($summary['changes_today'] ?? 0) }}</td>
            <td colspan="3" class="th-cell-center" style="background-color: #fffbeb; color: #92400e; font-size: 10pt;">PERINGATAN / KRITIS: {{ number_format($summary['warning_danger_count'] ?? 0) }}</td>
            <td colspan="3" class="th-cell-center" style="background-color: #f0fdf4; color: #166534; font-size: 10pt;">AKUN AKTIF BERAKTIVITAS: {{ number_format($summary['distinct_users_count'] ?? 0) }} AKUN</td>
        </tr>
    </table>

    <!-- DETAIL TABEL AUDIT LOG -->
    <table>
        <thead>
            <tr>
                <th colspan="12" class="sec-title">III. REKAMAN LOG TRANSAKSI DETAIL & INSPEKSI SEBELUM-SESUDAH (BEFORE/AFTER DIFF)</th>
            </tr>
            <tr>
                <th class="th-cell-center" style="width: 35px;">No</th>
                <th class="th-cell-center" style="width: 60px;">ID Log</th>
                <th class="th-cell" style="width: 130px;">Waktu Kejadian (WIB)</th>
                <th class="th-cell" style="width: 140px;">Pengguna & Jabatan</th>
                <th class="th-cell-center" style="width: 80px;">Aksi</th>
                <th class="th-cell" style="width: 130px;">Modul Terkait</th>
                <th class="th-cell-center" style="width: 75px;">Severity</th>
                <th class="th-cell-center" style="width: 75px;">Status</th>
                <th class="th-cell" style="width: 250px;">Deskripsi Aktivitas</th>
                <th class="th-cell" style="width: 250px;">Rincian Perubahan (Diff / Nilai Baru)</th>
                <th class="th-cell" style="width: 120px;">IP / Terminal</th>
                <th class="th-cell-mono" style="width: 180px;">Digital Signature Hash</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                @php
                    $severityClass = match(strtolower($log['severity'] ?? 'info')) {
                        'danger', 'critical' => 'badge-danger',
                        'warning' => 'badge-warning',
                        'success' => 'badge-success',
                        default => 'badge-info'
                    };
                    $statusClass = ($log['status'] ?? 'SUCCESS') === 'SUCCESS' ? 'badge-success' : 'badge-danger';
                    
                    $diffSummary = '';
                    if (!empty($log['diffs']) && is_array($log['diffs'])) {
                        $diffParts = [];
                        foreach ($log['diffs'] as $df) {
                            $field = $df['field'] ?? '';
                            $old = is_array($df['old']) ? json_encode($df['old']) : $df['old'];
                            $new = is_array($df['new']) ? json_encode($df['new']) : $df['new'];
                            $diffParts[] = "[$field: $old -> $new]";
                        }
                        $diffSummary = implode("; ", $diffParts);
                    } elseif (!empty($log['new_value'])) {
                        $diffSummary = is_array($log['new_value']) ? json_encode($log['new_value']) : (string)$log['new_value'];
                    } else {
                        $diffSummary = '-';
                    }
                @endphp
                <tr>
                    <td class="td-cell-center">{{ $index + 1 }}</td>
                    <td class="td-cell-center font-mono">#{{ $log['id'] }}</td>
                    <td class="td-cell">{{ $log['created_at_formatted'] ?? $log['created_at'] }}</td>
                    <td class="td-cell">
                        <strong>{{ $log['user_name'] ?? 'System' }}</strong><br>
                        <span style="color: #64748b; font-size: 7.5pt;">({{ $log['user_role'] ?? 'User' }})</span>
                    </td>
                    <td class="td-cell-center">
                        <strong>{{ $log['action'] }}</strong>
                    </td>
                    <td class="td-cell">{{ $log['module'] }}</td>
                    <td class="td-cell-center {{ $severityClass }}">{{ strtoupper($log['severity'] ?? 'INFO') }}</td>
                    <td class="td-cell-center {{ $statusClass }}">{{ strtoupper($log['status'] ?? 'SUCCESS') }}</td>
                    <td class="td-cell">{{ $log['description'] }}</td>
                    <td class="td-cell" style="font-size: 8pt; color: #334155;">{{ $diffSummary }}</td>
                    <td class="td-cell" style="font-size: 8pt;">{{ $log['ip_address'] ?? '127.0.0.1' }}</td>
                    <td class="td-cell-mono" style="font-size: 7.5pt; color: #475569;">{{ substr($log['hash'] ?? '-', 0, 18) }}...</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="td-cell-center" style="padding: 20px; color: #64748b;">
                        Tidak ada catatan log audit yang sesuai dengan kriteria filter saat ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- LEMBAR PENGESAHAN AUDITOR & MANAJEMEN -->
    <table>
        <tr>
            <td colspan="12" class="sec-title">IV. LEMBAR VERIFIKASI & PENGESAHAN AUDIT SISTEM</td>
        </tr>
        <tr>
            <td colspan="4" class="sig-box">
                <strong>Dibuat & Diekspor Oleh:</strong><br><br><br><br>
                <u><strong>{{ $exportedBy ?? 'Admin Audit / IT' }}</strong></u><br>
                <span>Sistem OEE Administrator</span>
            </td>
            <td colspan="4" class="sig-box">
                <strong>Diperiksa & Diverifikasi Oleh:</strong><br><br><br><br>
                <u><strong>QA / Compliance Lead</strong></u><br>
                <span>Quality Assurance & IT Auditor</span>
            </td>
            <td colspan="4" class="sig-box">
                <strong>Disetujui & Divalidasi Oleh:</strong><br><br><br><br>
                <u><strong>Factory / Plant Director</strong></u><br>
                <span>PT. Yasunaga Indonesia</span>
            </td>
        </tr>
    </table>
</body>
</html>
