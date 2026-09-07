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
                    <x:Name>Shift Handover Report</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                        <x:Print>
                            <x:ValidPrinterInfo/>
                            <x:PaperSizeIndex>9</x:PaperSizeIndex>
                            <x:Scale>95</x:Scale>
                        </x:Print>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
@endverbatim
    <style>
        body { font-family: Arial, Calibri, sans-serif; font-size: 8.5pt; color: #000000; }
        table { border-collapse: collapse; margin-bottom: 10px; width: 100%; }
        .doc-header-1 { font-family: Arial, sans-serif; font-size: 11pt; font-weight: bold; color: #000000; text-align: left; }
        .doc-header-2 { font-family: Arial, sans-serif; font-size: 8.5pt; font-weight: bold; color: #000000; text-align: left; }
        .doc-header-3 { font-family: Arial, sans-serif; font-size: 7.5pt; color: #444444; text-align: left; }
        .doc-title { font-family: Arial, sans-serif; font-size: 10.5pt; font-weight: bold; color: #0070C0; text-transform: uppercase; padding: 4px 0 6px 0; text-align: left; }
        .sec-title { font-family: Arial, sans-serif; font-size: 9pt; font-weight: bold; background-color: #0070C0; color: #FFFFFF; padding: 3px 6px; border: 0.5pt solid #005090; }
        .th-cell { background-color: #FFFFFF; font-weight: bold; border: 0.5pt solid #000000; text-align: left; padding: 3px 4px; font-size: 8pt; }
        .th-cell-num { background-color: #FFFFFF; font-weight: bold; border: 0.5pt solid #000000; text-align: right; padding: 3px 4px; font-size: 8pt; }
        .th-cell-center { background-color: #FFFFFF; font-weight: bold; border: 0.5pt solid #000000; text-align: center; padding: 3px 4px; font-size: 8pt; }
        .td-cell { border: 0.5pt solid #000000; padding: 3px 4px; vertical-align: middle; font-size: 8pt; }
        .td-cell-bold { border: 0.5pt solid #000000; padding: 3px 4px; font-weight: bold; font-size: 8pt; }
        .td-cell-num { border: 0.5pt solid #000000; padding: 3px 4px; text-align: right; font-size: 8pt; mso-number-format: "#,##0"; }
        .td-cell-dec { border: 0.5pt solid #000000; padding: 3px 4px; text-align: right; font-size: 8pt; mso-number-format: "0.0"; }
        .td-cell-pct { border: 0.5pt solid #000000; padding: 3px 4px; text-align: right; font-size: 8pt; }
        .td-cell-center { border: 0.5pt solid #000000; padding: 3px 4px; text-align: center; font-size: 8pt; }
        .meta-label { font-weight: bold; border: 0.5pt solid #000000; padding: 3px 4px; font-size: 8pt; text-align: right; background-color: #FFFFFF; }
        .meta-val { border: 0.5pt solid #000000; padding: 3px 4px; font-size: 8pt; }
        .total-row { font-weight: bold; background-color: #E2E8F0; border: 0.5pt solid #000000; }
        .sig-box { text-align: center; border: none; padding: 6px; font-size: 8pt; vertical-align: top; }
    </style>
</head>
<body>
    <!-- KOP DOKUMEN PT. YASUNAGA INDONESIA -->
    <table>
        <tr>
            <td colspan="15" class="doc-header-1">PT. YASUNAGA INDONESIA</td>
        </tr>
        <tr>
            <td colspan="15" class="doc-header-2">Engine Parts & Air Pump Manufacturing • PLT-01</td>
        </tr>
        <tr>
            <td colspan="15" class="doc-header-3">Jl. Modern Industri Raya Kav. 24 Kawasan Industri Modern Cikande, Nambo Ilir Kibin Serang Banten | Telp: (0254) 400306 | Email: prodcr@yasunaga.co.id</td>
        </tr>
        <tr><td colspan="15" style="border: none; height: 4px;"></td></tr>
        <tr>
            <td colspan="15" class="doc-title">LAPORAN KINERJA PRODUKSI & PERHITUNGAN OEE (SHIFT HANDOVER REPORT)</td>
        </tr>
    </table>

    <!-- METADATA INFORMATION TABLE -->
    <table>
        <tr>
            <td colspan="3" class="meta-label">Tanggal Produksi:</td>
            <td colspan="4" class="meta-val"><strong>{{ $header['formatted_date'] ?? '' }}</strong></td>
            <td colspan="3" class="meta-label">Lini Produksi:</td>
            <td colspan="5" class="meta-val"><strong>{{ $header['line_name'] ?? 'All Production Lines' }}</strong></td>
        </tr>
        <tr>
            <td colspan="3" class="meta-label">Shift Kerja:</td>
            <td colspan="4" class="meta-val"><strong>{{ $header['shift_name'] ?? '' }} ({{ $header['shift_time_window'] ?? '' }})</strong></td>
            <td colspan="3" class="meta-label">Supervisor In-Charge:</td>
            <td colspan="5" class="meta-val"><strong>{{ $header['supervisor_in_charge'] ?? '' }}</strong></td>
        </tr>
        <tr>
            <td colspan="3" class="meta-label">Baseline Target OEE:</td>
            <td colspan="4" class="meta-val">85.00% (World Class)</td>
            <td colspan="3" class="meta-label">Status Shift:</td>
            <td colspan="5" class="meta-val" style="color: #008000; font-weight: bold;">[● SHIFT COMPLETE]</td>
        </tr>
    </table>

    <!-- SECTION I: EXECUTIVE SUMMARY -->
    <table>
        <tr>
            <td colspan="15" class="sec-title">I. RINGKASAN PENCAPAIAN OEE & OUTPUT PRODUKSI (EXECUTIVE SUMMARY)</td>
        </tr>
        <tr>
            <th colspan="4" class="th-cell">Parameter Metrik</th>
            <th colspan="3" class="th-cell-num">Target Standar</th>
            <th colspan="3" class="th-cell-num">Pencapaian Aktual</th>
            <th colspan="2" class="th-cell-num">Deviasi Target</th>
            <th colspan="3" class="th-cell-center">Status Evaluasi</th>
        </tr>
        <tr>
            <td colspan="4" class="td-cell-bold">Availability (A) - Ketersediaan Mesin</td>
            <td colspan="3" class="td-cell-num">90.00%</td>
            <td colspan="3" class="td-cell-num" style="font-weight: bold;">{{ number_format($summary['availability'] ?? 0, 2) }}%</td>
            <td colspan="2" class="td-cell-num">{{ ($summary['deviations']['availability'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($summary['deviations']['availability'] ?? 0, 2) }}%</td>
            <td colspan="3" class="td-cell-center" style="font-weight: bold; color: {{ ($summary['availability'] ?? 0) >= 90 ? '#008000' : '#D97706' }};">{{ ($summary['availability'] ?? 0) >= 90 ? 'ON TARGET (BAIK)' : 'WARNING (DOWN)' }}</td>
        </tr>
        <tr>
            <td colspan="4" class="td-cell-bold">Performance (P) - Efisiensi Kecepatan</td>
            <td colspan="3" class="td-cell-num">95.00%</td>
            <td colspan="3" class="td-cell-num" style="font-weight: bold;">{{ number_format($summary['performance'] ?? 0, 2) }}%</td>
            <td colspan="2" class="td-cell-num">{{ ($summary['deviations']['performance'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($summary['deviations']['performance'] ?? 0, 2) }}%</td>
            <td colspan="3" class="td-cell-center" style="font-weight: bold; color: {{ ($summary['performance'] ?? 0) >= 95 ? '#008000' : '#D97706' }};">{{ ($summary['performance'] ?? 0) >= 95 ? 'ON TARGET (BAIK)' : 'WARNING (SPEED LOSS)' }}</td>
        </tr>
        <tr>
            <td colspan="4" class="td-cell-bold">Quality Rate (Q) - Kualitas Mutu</td>
            <td colspan="3" class="td-cell-num">99.00%</td>
            <td colspan="3" class="td-cell-num" style="font-weight: bold;">{{ number_format($summary['quality'] ?? 0, 2) }}%</td>
            <td colspan="2" class="td-cell-num">{{ ($summary['deviations']['quality'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($summary['deviations']['quality'] ?? 0, 2) }}%</td>
            <td colspan="3" class="td-cell-center" style="font-weight: bold; color: {{ ($summary['quality'] ?? 0) >= 99 ? '#008000' : '#DC2626' }};">{{ ($summary['quality'] ?? 0) >= 99 ? 'ON TARGET (BAIK)' : 'WARNING (HIGH DEFECT)' }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="4" class="td-cell-bold" style="background-color: #E2E8F0;">OVERALL EQUIPMENT EFFECTIVENESS (OEE)</td>
            <td colspan="3" class="td-cell-num" style="background-color: #E2E8F0; font-weight: bold;">85.00%</td>
            <td colspan="3" class="td-cell-num" style="background-color: #E2E8F0; font-weight: bold; font-size: 9.5pt;">{{ number_format($summary['overall_oee'] ?? 0, 2) }}%</td>
            <td colspan="2" class="td-cell-num" style="background-color: #E2E8F0; font-weight: bold;">{{ ($summary['deviations']['oee'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($summary['deviations']['oee'] ?? 0, 2) }}%</td>
            <td colspan="3" class="td-cell-center" style="background-color: #E2E8F0; font-weight: bold; color: {{ ($summary['overall_oee'] ?? 0) >= 85 ? '#008000' : '#DC2626' }};">{{ $summary['oee_badge'] ?? '[● OPTIMAL]' }}</td>
        </tr>
        <tr>
            <td colspan="3" class="td-cell-bold" style="background-color: #FFFF00; border: 0.5pt solid #000000;">Rincian Output Produksi:</td>
            <td colspan="2" class="td-cell-center" style="background-color: #FFFF00; font-weight: bold; border: 0.5pt solid #000000;">Total Output: {{ number_format($summary['total_output_pcs'] ?? 0) }} Pcs</td>
            <td colspan="3" class="td-cell-center" style="background-color: #FFFF00; font-weight: bold; border: 0.5pt solid #000000;">Good Output: {{ number_format($summary['good_output_pcs'] ?? 0) }} Pcs</td>
            <td colspan="3" class="td-cell-center" style="background-color: #FFFF00; font-weight: bold; border: 0.5pt solid #000000;">Reject (NG): {{ number_format($summary['reject_output_pcs'] ?? 0) }} Pcs</td>
            <td colspan="2" class="td-cell-center" style="background-color: #FFFF00; font-weight: bold; border: 0.5pt solid #000000;">Scrap: {{ number_format($summary['scrap_output_pcs'] ?? 0) }} Pcs</td>
            <td colspan="2" class="td-cell-center" style="background-color: #FFFF00; font-weight: bold; border: 0.5pt solid #000000;">Yield: {{ $summary['yield_rate_pct'] ?? 100 }}%</td>
        </tr>
    </table>

    <!-- SECTION II: CORE PRODUCTION MATRIX TABLE -->
    <table>
        <tr>
            <td colspan="15" class="sec-title">II. TABEL RINCIAN PERHITUNGAN OEE PER LINI PRODUKSI & MESIN</td>
        </tr>
        <tr>
            <th class="th-cell-center" style="width: 3%;">No</th>
            <th class="th-cell" style="width: 6%;">Line ID</th>
            <th class="th-cell" style="width: 10%;">Line Name</th>
            <th class="th-cell" style="width: 12%;">Machine</th>
            <th class="th-cell" style="width: 14%;">Product SKU</th>
            <th class="th-cell-num" style="width: 6%;">Plan (m)</th>
            <th class="th-cell-num" style="width: 6%;">Down (m)</th>
            <th class="th-cell-num" style="width: 6%;">Avail (%)</th>
            <th class="th-cell-num" style="width: 6%;">Speed (p/m)</th>
            <th class="th-cell-num" style="width: 7%;">Output (Pcs)</th>
            <th class="th-cell-num" style="width: 6%;">Perf (%)</th>
            <th class="th-cell-num" style="width: 6%;">Defect (Pcs)</th>
            <th class="th-cell-num" style="width: 6%;">Qual (%)</th>
            <th class="th-cell-num" style="width: 6%;">OEE (%)</th>
            <th class="th-cell-center" style="width: 8%;">Status</th>
        </tr>
        @foreach($rows as $i => $r)
            <tr>
                <td class="td-cell-center">{{ $i + 1 }}</td>
                <td class="td-cell-bold">{{ $r['line_id'] ?? '' }}</td>
                <td class="td-cell">{{ $r['line_name'] ?? '' }}</td>
                <td class="td-cell-bold">{{ $r['machine_code'] ?? '' }}</td>
                <td class="td-cell">{{ $r['product_sku'] ?? '' }}</td>
                <td class="td-cell-num">{{ number_format($r['planned_time_minutes'] ?? 0, 0) }}</td>
                <td class="td-cell-num">{{ number_format($r['downtime_minutes'] ?? 0, 0) }}</td>
                <td class="td-cell-pct">{{ number_format($r['availability_pct'] ?? 0, 2) }}%</td>
                <td class="td-cell-dec">{{ ($r['target_speed'] ?? 0) > 0 ? number_format($r['target_speed'], 1) : '-' }}</td>
                <td class="td-cell-num" style="font-weight: bold;">{{ number_format($r['total_output_pcs'] ?? 0) }}</td>
                <td class="td-cell-pct">{{ number_format($r['performance_pct'] ?? 0, 2) }}%</td>
                <td class="td-cell-num">{{ number_format($r['total_defect_pcs'] ?? 0) }}</td>
                <td class="td-cell-pct">{{ number_format($r['quality_pct'] ?? 0, 2) }}%</td>
                <td class="td-cell-pct" style="font-weight: bold;">{{ number_format($r['oee_pct'] ?? 0, 2) }}%</td>
                <td class="td-cell-center">{{ $r['status_badge'] ?? '' }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="5" class="td-cell-center font-bold" style="background-color: #E2E8F0;">TOTAL / RATA-RATA PABRIK:</td>
            <td class="td-cell-num" style="background-color: #E2E8F0;">{{ number_format($summary['total_planned_minutes'] ?? 0, 0) }}</td>
            <td class="td-cell-num" style="background-color: #E2E8F0;">{{ number_format($summary['total_downtime_minutes'] ?? 0, 0) }}</td>
            <td class="td-cell-pct" style="background-color: #E2E8F0;">{{ number_format($summary['availability'] ?? 0, 2) }}%</td>
            <td class="td-cell-center" style="background-color: #E2E8F0;">—</td>
            <td class="td-cell-num" style="background-color: #E2E8F0; font-weight: bold;">{{ number_format($summary['total_output_pcs'] ?? 0) }}</td>
            <td class="td-cell-pct" style="background-color: #E2E8F0;">{{ number_format($summary['performance'] ?? 0, 2) }}%</td>
            <td class="td-cell-num" style="background-color: #E2E8F0;">{{ number_format($summary['total_defect_pcs'] ?? 0) }}</td>
            <td class="td-cell-pct" style="background-color: #E2E8F0;">{{ number_format($summary['quality'] ?? 0, 2) }}%</td>
            <td class="td-cell-pct" style="background-color: #E2E8F0; font-weight: bold;">{{ number_format($summary['overall_oee'] ?? 0, 2) }}%</td>
            <td class="td-cell-center" style="background-color: #E2E8F0;">{{ $summary['oee_badge'] ?? '' }}</td>
        </tr>
    </table>

    <!-- SECTION III: SIX BIG LOSSES -->
    <table>
        <tr>
            <td colspan="15" class="sec-title">III. ANALISIS SIX BIG LOSSES (TPM LOSS BREAKDOWN)</td>
        </tr>
        <tr>
            <th class="th-cell-center" style="width: 4%;">No</th>
            <th colspan="4" class="th-cell" style="width: 28%;">Kategori Six Big Loss</th>
            <th colspan="3" class="th-cell" style="width: 22%;">Pilar OEE Terkait</th>
            <th colspan="2" class="th-cell-num" style="width: 15%;">Durasi (Menit)</th>
            <th colspan="2" class="th-cell-num" style="width: 15%;">Durasi (Jam)</th>
            <th colspan="3" class="th-cell-num" style="width: 16%;">% Kontribusi Loss</th>
        </tr>
        @foreach($sixLosses as $idx => $loss)
            <tr>
                <td class="td-cell-center">{{ $idx + 1 }}</td>
                <td colspan="4" class="td-cell-bold">{{ $loss['loss'] ?? '' }}</td>
                <td colspan="3" class="td-cell">{{ $loss['category'] ?? '' }} Loss</td>
                <td colspan="2" class="td-cell-dec">{{ number_format($loss['minutes'] ?? 0, 1) }}</td>
                <td colspan="2" class="td-cell-dec">{{ number_format($loss['hours'] ?? 0, 2) }}</td>
                <td colspan="3" class="td-cell-pct" style="font-weight: bold;">{{ number_format($loss['percentage'] ?? 0, 1) }}%</td>
            </tr>
        @endforeach
    </table>

    <!-- SECTION IV: MAJOR INCIDENTS LOG -->
    <table>
        <tr>
            <td colspan="15" class="sec-title">IV. LOG GANGGUAN MESIN & AKAR MASALAH (MAJOR INCIDENTS & RCA/CAPA)</td>
        </tr>
        <tr>
            <th colspan="2" class="th-cell" style="width: 11%;">ID Incident</th>
            <th colspan="2" class="th-cell" style="width: 12%;">Jam Trouble</th>
            <th colspan="3" class="th-cell" style="width: 17%;">Mesin & Line</th>
            <th class="th-cell-num" style="width: 8%;">Durasi (m)</th>
            <th colspan="3" class="th-cell" style="width: 26%;">Akar Masalah (5 Whys RCA)</th>
            <th colspan="4" class="th-cell" style="width: 26%;">Tindakan Perbaikan (CAPA) & PIC</th>
        </tr>
        @if(count($incidentLogs) === 0)
            <tr>
                <td colspan="15" class="td-cell-center" style="padding: 8px; color: #555555;">Tidak ada catatan gangguan breakdown selama shift ini (Zero Breakdown).</td>
            </tr>
        @else
            @foreach($incidentLogs as $inc)
                <tr>
                    <td colspan="2" class="td-cell-bold">{{ $inc['incident_id'] ?? '' }}</td>
                    <td colspan="2" class="td-cell">{{ $inc['time_window'] ?? '' }}</td>
                    <td colspan="3" class="td-cell">{{ $inc['machine_code'] ?? '' }} ({{ $inc['line_name'] ?? '' }})</td>
                    <td class="td-cell-num" style="font-weight: bold;">{{ number_format($inc['duration_minutes'] ?? 0, 0) }}</td>
                    <td colspan="3" class="td-cell">{{ $inc['root_cause'] ?? '' }}</td>
                    <td colspan="4" class="td-cell">{{ $inc['action_plan'] ?? '' }} (PIC: {{ $inc['pic'] ?? '' }})</td>
                </tr>
            @endforeach
        @endif
    </table>

    <!-- SECTION V: HANDOVER & SIGNATURES -->
    <table>
        <tr>
            <td colspan="15" class="sec-title">V. CATATAN SERAH TERIMA & PENGESAHAN LAPORAN</td>
        </tr>
        <tr>
            <td colspan="15" class="td-cell" style="background-color: #F8FAFC; padding: 6px;">
                <strong>Instruksi Prioritas Shift Handover:</strong>
                <div style="margin-top: 3px;">
                    @foreach($handoverNotes as $n)
                        <div style="padding: 1px 0;">• {{ $n }}</div>
                    @endforeach
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="15" style="text-align: right; border: none; font-size: 8pt; padding: 10px 10px 4px 0;">
                Tanggal: <strong>{{ $header['formatted_date'] ?? '' }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="5" class="sig-box" style="width: 33.3%;">
                Diserahkan Oleh,<br><br><br><br>
                ( <strong>{{ $header['supervisor_in_charge'] ?? '........................' }}</strong> )<br>
                <span style="font-size: 7.5pt; color: #555555;">Supervisor Shift In-Charge</span>
            </td>
            <td colspan="5" class="sig-box" style="width: 33.3%;">
                Diterima Oleh,<br><br><br><br>
                ( ............................................ )<br>
                <span style="font-size: 7.5pt; color: #555555;">Supervisor Incoming Shift</span>
            </td>
            <td colspan="5" class="sig-box" style="width: 33.3%;">
                Mengetahui / Diverifikasi:<br><br><br><br>
                ( ............................................ )<br>
                <span style="font-size: 7.5pt; color: #555555;">Production Manager</span>
            </td>
        </tr>
    </table>
</body>
</html>
