/**
 * i18n Localization & Translation Service
 * Supports Bahasa Indonesia (id) and Japanese (ja) with Manufacturing & Monozukuri Terminology
 */

const STORAGE_KEY = 'app_locale';

const translations = {
    // =========================================================================
    // 1. COMMON / UMUM / 共通
    // =========================================================================
    common: {
        id: {
            save: 'Simpan',
            saved: 'Tersimpan',
            cancel: 'Batal',
            edit: 'Edit',
            delete: 'Hapus',
            close: 'Tutup',
            search: 'Cari...',
            filter: 'Filter',
            apply: 'Terapkan',
            reset: 'Reset',
            refresh: 'Segarkan',
            details: 'Detail',
            action: 'Aksi',
            status: 'Status',
            date: 'Tanggal',
            time: 'Waktu',
            shift: 'Shift',
            line: 'Lini Produksi',
            machine: 'Mesin',
            product: 'Produk / SKU',
            target: 'Target',
            actual: 'Aktual',
            total: 'Total',
            all: 'Semua',
            loading: 'Memuat data...',
            no_data: 'Tidak ada data',
            success: 'Berhasil',
            error: 'Terjadi kesalahan',
            confirm_delete: 'Apakah Anda yakin ingin menghapus data ini?',
            yes: 'Ya',
            no: 'Tidak',
            notes: 'Catatan',
            export_excel: 'Export Excel',
            print: 'Cetak',
            print_preview: 'Cetak / Simpan PDF',
            minutes: 'Menit',
            hours: 'Jam',
            pieces: 'Pcs',
            optimal: 'Optimal',
            good: 'Baik',
            warning: 'Perhatian',
            critical: 'Kritis',
            running: 'BEROPERASI',
            downtime: 'DOWNTIME',
            idle: 'IDLE / MENUNGGU',
            maintenance: 'MAINTENANCE',
            setup: 'SETUP / DANDORI',
            resolved: 'SELESAI',
            in_progress: 'DALAM PENANGANAN',
            pending: 'MENUNGGU',
        },
        ja: {
            save: '保存',
            saved: '保存完了',
            cancel: 'キャンセル',
            edit: '編集',
            delete: '削除',
            close: '閉じる',
            search: '検索...',
            filter: '絞り込み',
            apply: '適用',
            reset: 'リセット',
            refresh: '更新',
            details: '詳細',
            action: '操作',
            status: '状態',
            date: '日付',
            time: '時間',
            shift: '直 (シフト)',
            line: 'ライン',
            machine: '設備・号機',
            product: '製品・品番',
            target: '目標数',
            actual: '実績数',
            total: '合計',
            all: 'すべて',
            loading: '読み込み中...',
            no_data: 'データがありません',
            success: '成功',
            error: 'エラーが発生しました',
            confirm_delete: 'このデータを削除してもよろしいですか？',
            yes: 'はい',
            no: 'いいえ',
            notes: '特記事項',
            export_excel: 'Excel出力',
            print: '印刷',
            print_preview: '印刷 / PDF保存',
            minutes: '分',
            hours: '時間',
            pieces: '個',
            optimal: '最適 (World Class)',
            good: '良好 (Good)',
            warning: '要改善 (Warning)',
            critical: '緊急対応要 (Critical)',
            running: '稼働中',
            downtime: '停止中',
            idle: '待機中',
            maintenance: '保全中',
            setup: '段取り中',
            resolved: '処置完了',
            in_progress: '対応中',
            pending: '未対応',
        }
    },

    // =========================================================================
    nav: {
        id: {
            brand_subtitle: 'ENGINE PARTS & AIR PUMP MFG',
            brand_system: 'PLANT-01 • MES STANDARD',
            section_operations: 'OPERASIONAL & INPUT DATA',
            dashboard: 'Dasbor',
            daily_input: 'Input Laporan Harian',
            ng_input: 'Input Laporan NG',
            monitoring: 'Monitoring Produksi',
            section_analytics: 'ANALISIS & SPC',
            machine_perf: 'Kinerja Mesin',
            line_perf: 'Lini Produksi',
            shift_team_perf: 'Kinerja Shift & Tim',
            shift_perf: 'Kinerja Shift',
            team_perf: 'Kinerja Tim',
            downtime_analysis: 'Analisis Downtime',
            quality_perf: 'Kinerja Kualitas',
            section_reports: 'MASTER DATA & LAPORAN',
            reports: 'Laporan & Ekspor Data',
            master_data: 'Master Data',
            section_system: 'SISTEM & AKSES',
            users: 'Manajemen Pengguna',
            settings: 'Pengaturan Sistem',
            database: 'Manajemen Basis Data',
            tv_mode: 'TV / Floor Display Mode',
            logout: 'Keluar',
            user_title: 'Super Administrator',
            user_role: 'Super Admin',
            live_active: 'LIVE',
            new_badge: 'NEW',
        },
        ja: {
            brand_subtitle: 'エンジン部品・エアーポンプ製造',
            brand_system: '第一工場 • MES製造実行システム',
            section_operations: '現場運用・実績入力',
            dashboard: 'ダッシュボード',
            daily_input: '日次生産実績入力',
            ng_input: '不良実績・NG入力',
            monitoring: 'ライン進捗モニター',
            section_analytics: '分析・統計的工程管理 (SPC)',
            machine_perf: '号機別稼働実績',
            line_perf: 'ライン別実績',
            shift_team_perf: '直・班別実績 (Shift & Team)',
            shift_perf: '直別実績 (シフト比較)',
            team_perf: '班別実績 (チーム比較)',
            downtime_analysis: '停止時間分析 (パレート)',
            quality_perf: '品質・良品率実績',
            section_reports: 'マスタ管理及び各種日報',
            reports: '帳票・実績日報出力',
            master_data: 'マスタ管理',
            section_system: 'システム管理・権限',
            users: 'ユーザー権限管理',
            settings: 'システム環境設定',
            database: 'データベース管理',
            tv_mode: 'アンドン・工場大型表示 (TV Mode)',
            logout: 'ログアウト',
            user_title: 'システム管理者',
            user_role: '管理者権限',
            live_active: '稼働中',
            new_badge: '新機能',
        }
    },

    // =========================================================================
    // 3. TOPBAR & FILTERS / トップバー・フィルター
    // =========================================================================
    header: {
        id: {
            all_lines: 'Semua Lini Produksi',
            all_machines: 'Semua Mesin',
            all_shifts: 'Semua Shift',
            today: 'Hari Ini',
            yesterday: 'Kemarin',
            last_7_days: '7 Hari Terakhir',
            last_30_days: '30 Hari Terakhir',
            this_month: 'Bulan Ini',
            apply_filter: 'Terapkan Filter',
            notifications: 'Notifikasi Sistem',
            new_notif: 'baru',
            mark_all_read: 'Tandai sudah dibaca',
            clear_all: 'Hapus Semua Notifikasi',
            no_notifications: 'Tidak ada notifikasi baru',
            theme_light: 'Mode Terang',
            theme_dark: 'Mode Gelap',
            add_record: '+ Entry Record',
            language: 'Bahasa',
            lang_id: 'Indonesia',
            lang_ja: '日本語',
        },
        ja: {
            all_lines: '全ライン (All Lines)',
            all_machines: '全号機 (All Machines)',
            all_shifts: '全直 (All Shifts)',
            today: '本日 (Today)',
            yesterday: '昨日 (Yesterday)',
            last_7_days: '直近7日間',
            last_30_days: '直近30日間',
            this_month: '今月 (This Month)',
            apply_filter: 'フィルター適用',
            notifications: 'システム通知',
            new_notif: '件の新規',
            mark_all_read: 'すべて既読にする',
            clear_all: '通知を全消去',
            no_notifications: '新しい通知はありません',
            theme_light: 'ライトモード',
            theme_dark: 'ダークモード',
            add_record: '+ 生産実績登録',
            language: '言語切替 (Language)',
            lang_id: 'インドネシア語 (ID)',
            lang_ja: '日本語 (JA)',
        }
    },

    // =========================================================================
    // 4. DASHBOARD & OEE CARDS / ダッシュボード・OEE指標
    // =========================================================================
    dashboard: {
        id: {
            title: 'Ringkasan Kinerja OEE & Produksi',
            subtitle: 'Pemantauan Efektivitas Peralatan Menyeluruh Secara Realtime',
            oee_score: 'OEE Score (Keseluruhan)',
            oee_formula: 'Availability × Performance × Quality',
            availability: 'Availability (Ketersediaan)',
            availability_sub: 'Rasio Jam Operasional Mesin',
            performance: 'Performance (Efisiensi)',
            performance_sub: 'Rasio Kecepatan & Output Target',
            quality: 'Quality (Kualitas Mutu)',
            quality_sub: 'Rasio Produk Baik (OK vs NG)',
            world_class_benchmark: 'Standar World Class: 85.0%',
            target_output: 'Target Output',
            total_output: 'Total Produksi',
            good_output: 'Produk Baik (OK)',
            defect_output: 'Produk Cacat (NG)',
            scrap_output: 'Scrap (Dibuang)',
            planned_time: 'Waktu Direncanakan',
            operating_time: 'Waktu Beroperasi',
            downtime_loss: 'Total Waktu Henti',
            six_big_losses: 'Six Big Losses Pareto Breakdown',
            six_losses_sub: 'Analisis 6 Kerugian Terbesar Berdasarkan Durasi',
            equipment_failure: 'Equipment Failure (Kerusakan Mesin)',
            setup_adjustment: 'Setup & Adjustment (Penyetelan & Dandori)',
            idling_stops: 'Idling & Minor Stops (Berhenti Singkat / Chocotei)',
            reduced_speed: 'Reduced Speed (Penurunan Kecepatan)',
            process_defects: 'Process Defects (Cacat Proses)',
            reduced_yield: 'Reduced Yield / Scrap (Penurunan Hasil Awal)',
            major_incidents: 'Major Incident Log Table (Downtime & Trouble)',
            incidents_sub: 'Downtime signifikan dengan threshold > 5 menit',
            root_cause: 'Root Cause (RCA)',
            action_plan: 'Action Plan (CAPA)',
            recent_production: 'Riwayat Log Produksi Terbaru',
            live_status: 'Status Lini Saat Ini',
        },
        ja: {
            title: 'OEE設備総合効率及び生産サマリー',
            subtitle: 'リアルタイム総合設備効率・稼働状況モニタリング',
            oee_score: 'OEE (設備総合効率)',
            oee_formula: '時間稼働率 × 性能稼働率 × 良品率',
            availability: '時間稼働率 (Availability)',
            availability_sub: '実稼働時間 / 計画負荷時間',
            performance: '性能稼働率 (Performance)',
            performance_sub: '基準サイクルタイム / 実績サイクル',
            quality: '良品率 (Quality Rate)',
            quality_sub: '良品数 / 総生産数量',
            world_class_benchmark: 'ワールドクラス標準目標: 85.0%',
            target_output: '目標計画数',
            total_output: '総生産実績数',
            good_output: '良品実績数 (OK)',
            defect_output: '不良実績数 (NG)',
            scrap_output: '廃棄スクラップ数',
            planned_time: '計画稼働時間',
            operating_time: '実稼働時間',
            downtime_loss: '総停止時間 (ロス)',
            six_big_losses: '6大ロス パレート分析 (Six Big Losses)',
            six_losses_sub: '停止時間規模順の要因別ブレークダウン',
            equipment_failure: '設備故障ロス (突発故障・機械トラブル)',
            setup_adjustment: '段取り・調整ロス (型替・段取り替え)',
            idling_stops: '空転・チョコ停ロス (一時停止・詰まり)',
            reduced_speed: '速度低下ロス (設計速度未満の運転)',
            process_defects: '工程不良ロス (加工不良・規格外)',
            reduced_yield: '歩留まりロス (立上がり時スクラップ)',
            major_incidents: '主要設備トラブル・停止履歴一覧',
            incidents_sub: '停止時間5分以上の重大トラブル履歴',
            root_cause: '要因分析・根本原因 (RCA)',
            action_plan: '是正処置・再発防止策 (CAPA)',
            recent_production: '直近の生産実績ログ一覧',
            live_status: '現在のライン稼働ステータス',
        }
    },

    // =========================================================================
    // 5. INPUT FORMS (DAILY PRODUCTION & NG) / 実績入力
    // =========================================================================
    input: {
        id: {
            entry_title: 'Input Laporan Produksi Harian',
            entry_subtitle: 'Pencatatan data output, jam kerja, dan parameter OEE lini',
            select_line: 'Pilih Lini Produksi',
            select_machine: 'Pilih Mesin',
            select_product: 'Pilih SKU Produk',
            select_shift: 'Pilih Shift Kerja',
            production_date: 'Tanggal Produksi',
            target_qty: 'Target Produksi (Pcs)',
            total_qty: 'Total Output Aktual (Pcs)',
            good_qty: 'Jumlah Produk OK (Pcs)',
            reject_qty: 'Jumlah Produk NG (Pcs)',
            planned_time: 'Planned Operating Time (Menit)',
            planned_downtime: 'Planned Downtime / Istirahat (Menit)',
            unplanned_downtime: 'Unplanned Downtime (Menit)',
            trouble_description: 'Uraian Masalah / Trouble',
            trouble_action: 'Tindakan Penanganan',
            operator_name: 'Nama Operator / Leader',
            save_record: 'Simpan Data Produksi',
            edit_record: 'Perbarui Data Produksi',
            delete_record: 'Hapus Data Produksi',
            ng_entry_title: 'Input Data NG & Kualitas',
            ng_entry_subtitle: 'Registrasi data defect mutu dan klasifikasi jenis reject',
            defect_type: 'Jenis Cacat / Kategori NG',
            defect_qty: 'Jumlah Reject (Pcs)',
            ng_disposition: 'Disposisi (Scrap / Rework)',
            ng_station: 'Stasiun / Pos Pemeriksaan',
            submit_ng: 'Simpan Laporan NG',
            slip_print_btn: 'Cetak Slip Laporan',
        },
        ja: {
            entry_title: '日次生産実績データ入力',
            entry_subtitle: 'ライン生産量、稼働時間及びOEEパラメータの登録',
            select_line: '製造ラインを選択',
            select_machine: '設備・号機を選択',
            select_product: '生産品番(SKU)を選択',
            select_shift: '勤務直(シフト)を選択',
            production_date: '生産実施日',
            target_qty: '計画目標数 (Pcs)',
            total_qty: '総生産実績数 (Pcs)',
            good_qty: '良品数 (OK数)',
            reject_qty: '不良数 (NG数)',
            planned_time: '計画操業時間 (分)',
            planned_downtime: '計画停止・休憩時間 (分)',
            unplanned_downtime: '突発停止・トラブル時間 (分)',
            trouble_description: '発生トラブル・現象内容',
            trouble_action: '実施した処置・対策',
            operator_name: '作業者 / 班長氏名',
            save_record: '生産実績を登録する',
            edit_record: '生産実績を更新する',
            delete_record: '実績データを削除する',
            ng_entry_title: '品質不良(NG)実績登録',
            ng_entry_subtitle: '検査工程における不具合現象及び内訳の記録',
            defect_type: '不良項目・不具合現象',
            defect_qty: '不良数量 (Pcs)',
            ng_disposition: '判定処置 (廃棄 / 手直し)',
            ng_station: '検査工程・ステーション',
            submit_ng: '不良データを登録する',
            slip_print_btn: '個別実績伝票を印刷',
        }
    },

    // =========================================================================
    // 6. REPORTS & EXPORT / 帳票・日報出力
    // =========================================================================
    reports: {
        id: {
            main_title: 'Reports & Export Center',
            main_subtitle: 'Pusat Pelaporan Kinerja Produksi, Kualitas, dan Keandalan Mesin',
            tab_prod: '1. Laporan Produksi & OEE',
            tab_ng: '2. Laporan NG & Kualitas',
            tab_trouble: '3. Laporan Trouble & Downtime',
            btn_print_prod: 'Cetak Laporan OEE',
            btn_export_prod: 'Export Excel OEE',
            btn_print_ng: 'Cetak Laporan NG',
            btn_export_ng: 'Export Excel NG',
            btn_print_trouble: 'Cetak Laporan Trouble',
            btn_export_trouble: 'Export Excel Trouble',
            exec_summary: 'Ringkasan Eksekutif Kinerja',
            matrix_title: 'Tabel Matriks Kinerja Lini & Mesin',
            handover_title: 'Catatan Serah Terima Shift & Rencana Aksi',
            handover_sub: 'Protokol Serah Terima Standar MES Operasional',
            incident_kpi_total: 'Total Insiden',
            incident_kpi_loss: 'Total Waktu Henti',
            incident_kpi_avg: 'Rata-rata Durasi',
            trouble_kpi_events: 'Total Kejadian Trouble',
            trouble_kpi_duration: 'Total Durasi Downtime',
            trouble_kpi_mttr: 'Rata-rata MTTR',
            trouble_kpi_unplanned: 'Unplanned Breakdown',
            trouble_kpi_planned: 'Planned Maintenance',
            sig_prepared_by: 'Diserahkan Oleh (Shift Selesai)',
            sig_received_by: 'Diterima Oleh (Shift Berikutnya)',
            sig_approved_by: 'Mengetahui / Diverifikasi',
            sig_operator: 'Operator / Leader',
            sig_qc: 'QC Inspector',
            sig_supervisor: 'Supervisor In-Charge',
            sig_manager: 'Production Manager',
            sig_maintenance: 'Teknisi Maintenance',
            ng_summary_total: 'Total Produk Reject',
            ng_summary_rate: 'Rata-rata Defect Rate',
            ng_summary_dominant: 'Defect Paling Dominan',
            ng_summary_cost: 'Estimasi Biaya Defect',
        },
        ja: {
            main_title: '製造実績・品質管理 帳票センター',
            main_subtitle: 'OEE設備総合効率、品質不良、設備停止履歴の公式報告書出力',
            tab_prod: '1. 生産実績及びOEE日報',
            tab_ng: '2. 品質不良・NG管理レポート',
            tab_trouble: '3. 設備トラブル・停止履歴日報',
            btn_print_prod: 'OEE日報を印刷 (A4)',
            btn_export_prod: 'OEE Excel出力',
            btn_print_ng: 'NGレポート印刷 (A4)',
            btn_export_ng: 'NG Excel出力',
            btn_print_trouble: 'トラブル日報印刷 (A4)',
            btn_export_trouble: 'トラブル Excel出力',
            exec_summary: '製造実績エグゼクティブサマリー',
            matrix_title: 'ライン別・号機別 OEE詳細計算マトリクス',
            handover_title: '直間引継ぎ事項及び次回シフト指示',
            handover_sub: 'MES公式シフト間コミュニケーション規程',
            incident_kpi_total: '総インシデント件数',
            incident_kpi_loss: '総停止時間ロス',
            incident_kpi_avg: '平均停止時間',
            trouble_kpi_events: '総トラブル発生件数',
            trouble_kpi_duration: '総ダウンタイム時間',
            trouble_kpi_mttr: '平均復旧時間 (MTTR)',
            trouble_kpi_unplanned: '突発故障停止 (Unplanned)',
            trouble_kpi_planned: '定期保全停止 (Planned)',
            sig_prepared_by: '引渡担当者 (前直リーダー)',
            sig_received_by: '受取確認者 (後直リーダー)',
            sig_approved_by: '承認・査閲者 (製造課長)',
            sig_operator: '班長 / 担当作業者',
            sig_qc: '品質保証・検査担当',
            sig_supervisor: '製造職長 / 監督者',
            sig_manager: '製造部長 / 工場長',
            sig_maintenance: '保全・技術担当者',
            ng_summary_total: '総不良数量',
            ng_summary_rate: '平均不良率',
            ng_summary_dominant: '最頻出不良項目',
            ng_summary_cost: '推定損失コスト',
        }
    },

    // =========================================================================
    // 7. MONITORING & ANDON TV DISPLAY / アンドン大型表示
    // =========================================================================
    monitoring: {
        id: {
            title: 'Live Line Telemetry & Production Monitoring',
            subtitle: 'Status real-time setiap stasiun kerja dan kecepatan aktual mesin',
            running_machines: 'Mesin Beroperasi',
            downtime_machines: 'Mesin Downtime',
            avg_speed: 'Kecepatan Rata-rata',
            speed_unit: 'pcs/menit',
            hourly_output: 'Grafik Output Per Jam',
            station_telemetry: 'Telemetri Stasiun Kerja',
            andon_title: 'PLANT FLOOR ANDON MONITOR',
            fullscreen_btn: 'Tampilan Layar Penuh (F11)',
            exit_fullscreen: 'Keluar Layar Penuh',
        },
        ja: {
            title: 'ライン稼働状況 リアルタイム監視 (Andon)',
            subtitle: '各工程の稼働ステータス及び設備サイクルの即時モニタリング',
            running_machines: '稼働中設備数',
            downtime_machines: '停止中設備数',
            avg_speed: '平均生産速度',
            speed_unit: '個/分',
            hourly_output: '時間帯別生産推移グラフ',
            station_telemetry: '工程別稼働テレメトリ',
            andon_title: '工場フロア大型アンドンモニター',
            fullscreen_btn: '全画面表示モード (F11)',
            exit_fullscreen: '全画面解除',
        }
    },

    // =========================================================================
    // 8. PRODUCTION SLIP PRINT / 個別実績伝票
    // =========================================================================
    slip: {
        id: {
            doc_title: 'SLIP LAPORAN HASIL PRODUKSI & OEE',
            doc_number: 'No. Dokumen',
            line_label: 'Lini Produksi',
            machine_label: 'Mesin / Station',
            product_label: 'Part Name / SKU',
            shift_label: 'Shift Kerja',
            prod_date: 'Tanggal Produksi',
            ideal_cycle: 'Ideal Cycle Time',
            actual_cycle: 'Actual Cycle Time',
            oee_badge_title: 'EVALUASI KINERJA OEE',
            output_defect_summary: 'I. REKAPITULASI OUTPUT & KUALITAS MUTU (QUALITY)',
            target_col: 'Target Output',
            actual_col: 'Aktual Total',
            good_col: 'Produk Baik (OK)',
            reject_col: 'Cacat (NG)',
            scrap_col: 'Scrap',
            reject_rate_col: 'Defect Rate (%)',
            yield_rate_col: 'Yield Rate (%)',
            time_allocation_summary: 'II. ALOKASI WAKTU KERJA & DOWNTIME (AVAILABILITY)',
            planned_time_col: 'Planned Time (m)',
            planned_down_col: 'Planned Down (m)',
            operating_run_col: 'Operating Run Time (m)',
            unplanned_down_col: 'Unplanned Down (m)',
            trouble_notes: 'CATATAN OPERASIONAL & TROUBLE MESIN',
            sig_leader: 'Dibuat Oleh (Leader / Operator)',
            sig_qc: 'Diperiksa Oleh (QC Inspector)',
            sig_spv: 'Disetujui Oleh (Supervisor)',
        },
        ja: {
            doc_title: '生産実績及びOEE設備管理票 (スリップ)',
            doc_number: '管理番号',
            line_label: '製造ライン',
            machine_label: '設備・ステーション',
            product_label: '製品名・品番 (SKU)',
            shift_label: '勤務直 (シフト)',
            prod_date: '生産実施日',
            ideal_cycle: '基準サイクルタイム',
            actual_cycle: '実績サイクルタイム',
            oee_badge_title: '総合設備効率 (OEE) 評価結果',
            output_defect_summary: 'I. 生産実績及び品質管理集計 (Quality Summary)',
            target_col: '計画目標数',
            actual_col: '総生産実績数',
            good_col: '良品数 (OK)',
            reject_col: '不良数 (NG)',
            scrap_col: '廃棄数',
            reject_rate_col: '不良率 (%)',
            yield_rate_col: '歩留まり率 (%)',
            time_allocation_summary: 'II. 稼働時間配分及び停止内訳 (Availability Summary)',
            planned_time_col: '計画操業時間 (分)',
            planned_down_col: '計画停止時間 (分)',
            operating_run_col: '実稼働時間 (分)',
            unplanned_down_col: '突発停止時間 (分)',
            trouble_notes: '特記事項・発生トラブル記録',
            sig_leader: '作成担当 (班長・リーダー)',
            sig_qc: '品質確認 (品証・検査担当)',
            sig_spv: '承認者 (製造職長・課長)',
        }
    }
};

// =========================================================================
// MONOZUKURI MASTER DICTIONARY: 100% Comprehensive Manufacturing Lexicon
// Covers All Pages, Sub-Tabs, Dynamic Modals, Popups, Tables, Forms, Alerts
// =========================================================================
const MONOZUKURI_MASTER_DICTIONARY = [
    // --- OFFICIAL A4 PRINT & EXPORT REPORT HEADERS ---
    ["LAPORAN DATA TROUBLE & DOWNTIME", "設備停止・トラブル実績報告書"],
    ["LAPORAN RIWAYAT TROUBLE & DOWNTIME MESIN PRODUKSI", "設備トラブル・停止履歴実績日報"],
    ["LAPORAN RIWAYAT TROUBLE & DOWNTIME MESIN", "設備トラブル・停止履歴実績日報"],
    ["LAPORAN KINERJA PRODUKSI & PERHITUNGAN OEE (SHIFT HANDOVER REPORT)", "製造実績及び総合設備効率 (OEE) 引継ぎ日報"],
    ["LAPORAN KINERJA PRODUKSI & OEE", "製造実績及び総合設備効率 (OEE) 日報"],
    ["LAPORAN DATA NG & DEFECT KUALITAS PRODUKSI", "品質不良及び欠陥分析報告書"],
    ["LAPORAN DATA NG & DEFECT MUTU", "品質不良・NG検査実績報告書"],
    ["SLIP LAPORAN HASIL PRODUKSI & OEE", "生産実績及びOEE設備管理票"],
    ["Form: A4-Landscape Trouble & Downtime Report", "様式: A4横 設備停止・トラブル実績報告書"],
    ["Form: A4-Portrait Shift Handover", "様式: A4縦 直間引継ぎ報告書"],
    ["Form: A4-Landscape QC Defect Inspection Report", "様式: A4横 品質検査・不良分析報告書"],
    ["Form: A4-Portrait Production & OEE Result Slip", "様式: A4縦 個別生産実績・OEE管理スリップ"],
    ["I. RINGKASAN EKSEKUTIF WAKTU HENTI (DOWNTIME & RELIABILITY KPI)", "I. 設備停止・信頼性KPIサマリー (Downtime & Reliability KPI)"],
    ["I. RINGKASAN PENCAPAIAN OEE & OUTPUT PRODUKSI (EXECUTIVE SUMMARY)", "I. 生産実績及びOEE達成状況サマリー (Executive Summary)"],
    ["I. RINGKASAN EKSEKUTIF PENCAPAIAN OEE & OUTPUT PRODUKSI", "I. 生産実績及びOEE達成状況サマリー (Executive Summary)"],
    ["I. RINGKASAN PENCAPAIAN KUALITAS & DEFECT (QC EXECUTIVE SUMMARY)", "I. 品質実績・不良集計サマリー (QC Executive Summary)"],
    ["I. REKAPITULASI OUTPUT & KUALITAS MUTU (QUALITY)", "I. 生産実績及び品質管理集計 (Quality Summary)"],
    ["II. TABEL RINCIAN DATA TROUBLE MESIN & TINDAKAN PERBAIKAN (CAPA)", "II. 設備トラブル明細及び是正処置 (CAPA) 報告"],
    ["II. TABEL RINCIAN RIWAYAT TROUBLE MESIN & TINDAKAN PERBAIKAN (CAPA)", "II. 設備トラブル明細及び是正処置 (CAPA) 報告"],
    ["II. TABEL RINCIAN PERHITUNGAN OEE PER LINI PRODUKSI & MESIN", "II. ライン別・号機別 OEE詳細計算マトリクス"],
    ["II. TABEL REKAPITULASI RINCIAN DATA DEFECT PER LINE & PRODUK", "II. ライン別・製品別 不良詳細検査明細表"],
    ["II. STRUKTUR WAKTU KERJA & LOSS AVAILABILITY", "II. 稼働時間構造及び停止ロス内訳"],
    ["II. STRUKTUR WAKTU KERJA & DOWNTIME", "II. 稼働時間構造及び停止ロス内訳"],
    ["III. DISTRIBUSI KATEGORI MASALAH", "III. トラブル要因別内訳 (パレート分析)"],
    ["III. ANALISIS SIX BIG LOSSES (TPM LOSS BREAKDOWN)", "III. 6大ロス分析 (TPM Loss Breakdown)"],
    ["IV. TOP 5 MESIN DENGAN DOWNTIME TERTINGGI", "IV. 停止時間上位5号機 (ワースト5)"],
    ["IV. LOG GANGGUAN MESIN & AKAR MASALAH (MAJOR INCIDENTS & RCA/CAPA)", "IV. 重大設備トラブル記録及び要因・是正処置 (RCA/CAPA)"],
    ["V. INSTRUKSI SHIFT BERIKUTNYA & PENGESAHAN SERAH TERIMA", "V. 次直引継ぎ指示事項及び承認欄"],
    ["V. CATATAN SERAH TERIMA & PENGESAHAN LAPORAN", "V. 直間引継ぎ事項及び報告書承認"],
    ["Tabel Rekapitulasi Rincian Data Trouble & Downtime Mesin", "設備トラブル及び停止時間 詳細集計表"],
    ["Pengesahan Riwayat Trouble & Kontrol Reliabilitas Mesin (Maintenance Approval)", "保全履歴承認及び設備信頼性管理承認欄"],
    ["Standard Maintenance TPM & ISO Form", "TPM標準保全及びISO管理様式"],
    ["Distribusi Trouble per Kategori Masalah", "トラブル要因別内訳 (パレート)"],
    ["Top Mesin Terkendala & Rekomendasi CAPA", "停止ワースト号機及びCAPA推奨事項"],
    ["Pareto Loss Availability", "時間稼働ロス パレート分析"],
    ["Rekomendasi: Lakukan inspeksi preventif berkala dan standarisasi SOP penanganan mesin.", "推奨: 定期予防保全の徹底及び設備処置SOPの標準化を実施。"],
    ["Instruksi Prioritas Shift Handover:", "直間引継ぎ重点指示事項:"],
    ["Catatan Prioritas Handover:", "重点引継ぎ連絡事項:"],
    ["Catatan Kendala Operasional:", "操業上の特記事項:"],
    ["Total Kejadian (Calls)", "総発生件数 (回)"],
    ["Total Kejadian", "総発生件数"],
    ["Total Waktu Henti", "総停止時間"],
    ["Total Durasi Waktu Henti", "総停止時間"],
    ["Total Downtime (Menit)", "総停止時間 (分)"],
    ["Total Downtime (Jam)", "総停止時間 (時間)"],
    ["Rata-rata Penanganan (MTTR)", "平均復旧時間 (MTTR)"],
    ["Rata-Rata Penanganan (MTTR)", "平均復旧時間 (MTTR)"],
    ["Rata-rata MTTR (Menit)", "平均 MTTR (分)"],
    ["Unplanned Breakdown", "突発故障停止 (計画外)"],
    ["Planned Maintenance", "定期保全停止 (計画)"],
    ["Planned Maint:", "計画保全:"],
    ["Target Unplanned Breakdown:", "突発停止目標値:"],
    ["Total Waktu Henti (Downtime):", "総停止時間 (Downtime):"],
    ["Total Kejadian Trouble:", "総トラブル件数:"],
    ["Mesin Paling Terkendala", "最多停止設備 (ワースト号機)"],
    ["Kategori Masalah Dominan", "最多トラブル要因分類"],
    ["Semua Mesin Aman", "全設備正常稼働"],
    ["Zero Downtime Tercapai", "ゼロダウンタイム達成"],
    ["Zero Incident", "異常発生ゼロ"],
    ["Operasional 100% Lancar", "操業100%順調"],
    ["Target Max Breakdown", "最大停止許容目標"],
    ["Total Entri Trouble", "総トラブル登録件数"],
    ["Periode Tanggal:", "対象期間 (日付):"],
    ["Periode Tanggal", "対象期間 (日付)"],
    ["Lini Produksi:", "製造ライン:"],
    ["Lini Produksi", "製造ライン"],
    ["Shift Kerja:", "勤務直 (シフト):"],
    ["Shift Kerja", "勤務直 (シフト)"],
    ["Supervisor PIC:", "責任者 (職長):"],
    ["Supervisor PIC", "責任者 (職長)"],
    ["Supervisor In-Charge:", "管理監督者 (職長):"],
    ["Supervisor / QC PIC:", "品質責任者 / 検査職長:"],
    ["Supervisor / Maintenance:", "保全・監督責任者:"],
    ["Tanggal & Jam", "発生日時"],
    ["Tanggal & Waktu", "発生日時"],
    ["Tanggal & Shift", "検査日時・直"],
    ["Jam Kejadian", "発生時間帯"],
    ["Mesin Terkendala", "対象号機"],
    ["Line & Mesin", "ライン・号機"],
    ["Part / SKU", "製品名・品番"],
    ["Type Produk (SKU)", "製品種別 (品番)"],
    ["Gejala & Root Cause Masalah", "現象・根本原因"],
    ["Gejala & Root Cause Trouble", "現象・根本原因"],
    ["Akar Masalah (5 Whys RCA)", "根本原因 (5-Why なぜなぜ分析)"],
    ["Tindakan Perbaikan (CAPA)", "是正処置内容 (CAPA)"],
    ["Tindakan Perbaikan (CAPA) & PIC", "是正処置内容 (CAPA) & 担当者"],
    ["Tindakan CAPA", "是正処置 (CAPA)"],
    ["PIC / Tim", "担当 / チーム"],
    ["Tanggal Produksi:", "生産日:"],
    ["TANGGAL PRODUKSI:", "生産実施日:"],
    ["TANGGAL AUDIT DEFECT:", "品質監査実施日:"],
    ["TANGGAL:", "実施日:"],
    ["Tanggal Verifikasi:", "確認・承認日:"],
    ["Tanggal Cetak:", "印刷発行日:"],
    ["Tanggal Cetak / Verifikasi:", "印刷・確認日:"],
    ["Total Keseluruhan Waktu Henti:", "総停止時間 合計:"],
    ["TOTAL KESELURUHAN WAKTU HENTI:", "総停止時間 総合計:"],
    ["TOTAL KESELURUHAN WAKTU HENTI DOWNTIME:", "総停止時間 総合計:"],
    ["Total Output Produksi:", "生産数量集計:"],
    ["Total Output Produksi", "総生産数量"],
    ["Good Output (OK)", "良品数量 (OK)"],
    ["Total Reject / NG", "総不良数量 (NG)"],
    ["Total Reject/Scrap:", "総不良数・廃棄:"],
    ["Total Reject (NG):", "総不良数 (NG):"],
    ["Defect Rate (%)", "不良率 (%)"],
    ["Quality Yield Rate (%)", "直行率・良品歩留まり率 (%)"],
    ["Rincian Komponen NG:", "NG部品別内訳:"],
    ["Rincian Output Produksi:", "出来高実績内訳:"],
    ["Wajib OEE:", "主要部品 (OEE対象):"],
    ["Pelengkap Non-OEE:", "締結部品 (非OEE):"],
    ["Wajib OEE (A/R/C)", "主要部品 (Assy/Rod/Cap)"],
    ["Pelengkap (B/Bs/N/P)", "締結部品 (Bolt/Bush/Nut/Pin)"],
    ["Masalah / Kategori Defect", "不良現象・要因分類"],
    ["Kategori Masalah", "トラブル要因分類"],
    ["Frekuensi", "発生頻度"],
    ["Durasi (Menit)", "停止時間 (分)"],
    ["Durasi (Jam)", "停止時間 (時間)"],
    ["Kode Mesin", "号機コード"],
    ["Waktu Henti", "停止時間"],
    ["Ideal Cycle Time Desain", "基準サイクルタイム (設計)"],
    ["Standar Kecepatan Mesin", "基準設備速度"],
    ["Planned Production Time (Shift Penuh)", "計画操業時間 (直全体)"],
    ["Break / Istirahat Terencana", "計画休憩・除外時間"],
    ["Operating Run Time (Mesin Berjalan)", "実稼働時間 (設備運転時間)"],
    ["Unplanned Downtime (Waktu Henti Kendala)", "突発停止時間 (トラブル停止)"],
    ["Total Jadwal Shift", "直総所定時間"],
    ["Planned Break", "計画休憩"],
    ["Kerugian Material", "材料ロス"],
    ["Dibuat Oleh (Shift Leader):", "作成 (直リーダー):"],
    ["Dibuat Oleh (Leader Shift)", "作成 (直リーダー)"],
    ["Dibuat Oleh (Operator / Leader Shift):", "作成 (担当者 / 直リーダー):"],
    ["Dibuat Oleh (Operator / Leader):", "作成 (担当リーダー):"],
    ["Dibuat Oleh (Operator Produksi):", "作成 (製造担当者):"],
    ["Dibuat Oleh (Operator)", "作成 (製造担当者)"],
    ["Ditangani Oleh (Teknisi / Maint):", "保全処置担当 (技術・保全):"],
    ["Ditangani Oleh (Teknisi / Maintenance):", "保全処置担当 (技術・保全):"],
    ["Ditangani Oleh (Maintenance)", "保全処置担当 (技術・保全)"],
    ["Diperiksa Oleh (QC Inspector / Leader):", "検査確認 (品証リーダー):"],
    ["Diperiksa Oleh (QC Inspector):", "検査確認 (品証担当):"],
    ["Diperiksa Oleh (QC Leader)", "検査確認 (品証リーダー)"],
    ["Mengetahui / Disetujui (Supervisor):", "承認 (製造職長・課長):"],
    ["Mengetahui / Disetujui (Supervisor Produksi):", "承認 (製造職長・課長):"],
    ["Disetujui Oleh (Supervisor Produksi / QC):", "承認 (製造・品証職長):"],
    ["Disetujui Oleh (Supervisor):", "承認 (製造職長・課長):"],
    ["Disetujui Oleh (Supervisor)", "承認 (製造職長・課長)"],
    ["Mengetahui / Disetujui:", "承認 (製造課長):"],
    ["Diserahkan Oleh (Shift Selesai):", "引渡し (前直リーダー):"],
    ["Diserahkan Oleh,", "引渡し (前直担当):"],
    ["Diterima Oleh (Shift Berikutnya):", "受領 (後直リーダー):"],
    ["Diterima Oleh,", "受領 (後直担当):"],
    ["Mengetahui / Diverifikasi:", "確認・査閲 (製造課長):"],
    ["Leader / Operator Produksi", "製造直リーダー / 担当"],
    ["Maintenance & Engineering", "保全技術課"],
    ["Supervisor Produksi & Maintenance", "製造監督職長・保全責任者"],
    ["Quality Assurance & Control", "品質保証・検査課"],
    ["Supervisor / Dept Head", "製造課長 / 部門長"],
    ["Quality Control Inspector", "品質管理・検査員"],
    ["Supervisor Produksi & QC", "製造・品証職長"],
    ["Supervisor Shift In-Charge", "前直製造職長"],
    ["Supervisor Incoming Shift", "後直製造職長"],
    ["Production Manager", "製造課長 / 工場長"],
    ["Leader Shift Produksi", "製造直リーダー"],
    ["Teknisi Maintenance", "保全技術担当"],
    ["Zero Breakdown! Semua mesin beroperasi normal tanpa catatan waktu henti (downtime) pada periode ini.", "全設備正常稼働中 (ゼロトラブル)。対象期間における停止記録はありません。"],
    ["Zero Breakdown! Tidak Ada Riwayat Trouble Mesin", "全設備正常稼働 (設備トラブル発生ゼロ)"],
    ["Semua mesin beroperasi lancar tanpa catatan downtime pada filter periode tanggal, line, atau shift yang dipilih.", "選択された期間・ライン・シフトにおいて、全ての設備が停止なく順調に稼働しました。"],
    ["Tidak ada catatan gangguan breakdown selama shift ini (Zero Breakdown).", "当直における設備停止トラブルの記録はありません (ゼロダウンタイム)。"],
    ["Tidak ada catatan data defect / NG untuk kriteria filter yang dipilih.", "選択された条件において不良・NGの発生記録はありません。"],
    ["Tidak ada data produksi pada periode ini.", "対象期間の生産データはありません。"],
    ["Tidak ada data kategori trouble untuk periode ini.", "対象期間のトラブル分類データはありません。"],
    ["Semua mesin berada dalam performa optimal (Zero Trouble).", "全ての号機が最適な状態で稼働しています (トラブルゼロ)。"],
    ["Parameter Metrik", "管理指標・項目"],
    ["Target Standar", "基準目標値"],
    ["Pencapaian Aktual", "実績値"],
    ["Status Evaluasi", "評価判定"],
    ["Availability (A) - Ketersediaan Mesin", "時間稼働率 (Availability - A)"],
    ["Performance (P) - Efisiensi Kecepatan", "性能稼働率 (Performance - P)"],
    ["Quality Rate (Q) - Kualitas Mutu", "良品率 (Quality Rate - Q)"],
    ["OVERALL EQUIPMENT EFFECTIVENESS (OEE)", "総合設備効率 (OEE)"],
    ["Good Output:", "良品数 (OK):"],
    ["Total Output:", "総生産数:"],
    ["Total Reject/Scrap:", "総不良数・廃棄:"],
    ["Total Reject (NG):", "総不良数 (NG):"],
    ["Yield:", "歩留まり率:"],
    ["RATA-RATA / TOTAL PABRIK:", "工場全体 合計 / 平均:"],
    ["TOTAL / RATA-RATA PABRIK:", "工場全体 合計 / 平均:"],
    ["Kategori Six Big Loss", "6大ロス分類"],
    ["Pilar OEE Terkait", "該当OEE要素"],
    ["% Kontribusi Loss", "ロス寄与率 (%)"],
    ["ID Incident", "インシデント ID"],
    ["Jam Trouble", "発生時間"],
    ["Mesin & Line", "号機 & ライン"],
    ["Akar Masalah (5 Whys RCA)", "根本原因 (5-Why なぜなぜ分析)"],
    ["Tindakan Perbaikan (CAPA) & PIC", "是正処置 (CAPA) & 担当者"],
    ["Catatan Prioritas Handover:", "重点引継ぎ連絡事項:"],
    ["Subtotal Waktu Henti:", "停止時間 小計:"],
    ["Kali Kejadian", "回発生"],
    ["Kali Trouble", "回トラブル"],
    ["Kejadian Trouble", "件のトラブル"],
    ["Menit / Trouble", "分 / 件"],
    ["Menit / Shift", "分 / 直"],
    ["jam", "時間"],
    ["Jam", "時間"],
    ["menit", "分"],
    ["Menit", "分"],
    ["detik / pc", "秒 / 個"],
    ["Detik", "秒"],
    ["Pcs", "個"],
    ["pcs", "個"],
    ["calls", "件"],
    ["Entri Produksi", "件の生産実績"],
    ["Entri", "件"],
    ["Mesin", "設備・機械"],
    ["Dies", "金型・ダイス"],
    ["Listrik", "電気・制御"],
    ["Material", "材料・部材"],
    ["Metode", "作業方法・手順"],
    ["Manpower", "作業者・人"],
    ["PRODUKTIF", "生産中 (実稼働)"],
    ["DOWNTIME LOSS", "停止ロス発生"],
    ["ZERO BREAKDOWN", "突発停止ゼロ"],
    ["DEFECT DETECTED", "不良発生あり"],
    ["ZERO DEFECT", "不良発生ゼロ"],
    ["DISCARDED", "廃棄処理"],
    ["STANDARD SPEED", "基準速度達成"],
    ["SELESAI", "処置完了"],
    ["ONGOING", "対応中・継続中"],
    ["OK", "合格・完了"],
    ["ON TARGET (BAIK)", "目標達成 (良好)"],
    ["WARNING (DOWN)", "要改善 (停止ロス大)"],
    ["WARNING (SPEED LOSS)", "要改善 (速度低下)"],
    ["WARNING (HIGH DEFECT)", "要改善 (不良多発)"],
    ["ZERO TROUBLE", "トラブルゼロ"],
    ["NEED MONITOR", "要注意・監視"],
    ["GOOD SPEED", "復旧迅速 (良好)"],
    ["LONG REPAIR", "復旧時間超過 (要改善)"],
    ["OPTIMAL", "最適状態"],
    ["Laporan Data Trouble & Downtime", "設備停止・トラブル実績報告書"],
    ["Laporan Trouble dan Downtime Mesin", "設備トラブル及び停止時間日報"],
    ["Laporan Produksi dan OEE", "生産実績及びOEE日報"],
    ["Laporan Data NG dan Defect Mutu", "品質不良及び欠陥検査報告書"],
    ["Slip Laporan Hasil Produksi", "生産実績及びOEE管理スリップ"],
    // --- EXISTING MONOZUKURI MASTER DICTIONARY ENTRIES ---
    [
        "Real-Time Factory OEE Telemetry & Production Analytics",
        "リアルタイム工場OEEテレメトリ及び生産分析"
    ],
    [
        "Production Lines Core Performance Matrix",
        "ライン別 OEE稼働実績マトリクス"
    ],
    [
        "Comprehensive OEE factors, actual output, cycle time deviations & operational status per line",
        "ライン別 稼働時間・出来高実績・稼働ステータス詳細一覧"
    ],
    [
        "Macro KPIs Overview",
        "主要KPI概要サマリー"
    ],
    [
        "Six Big Losses Breakdown",
        "6大ロス パレート分析"
    ],
    [
        "Six Big Losses Pareto Breakdown",
        "6大ロス パレート分析"
    ],
    [
        "Machine Status Overview",
        "設備稼働状況サマリー"
    ],
    [
        "Major Incident Log Table (Downtime & Trouble)",
        "主要設備トラブル・停止履歴一覧"
    ],
    [
        "Major Incident Log Table",
        "主要設備トラブル・停止履歴一覧"
    ],
    [
        "Downtime signifikan dengan threshold > 5 menit",
        "停止時間5分以上の重大トラブル履歴"
    ],
    [
        "Root Cause (RCA)",
        "要因分析・根本原因 (RCA)"
    ],
    [
        "Action Plan (CAPA)",
        "是正処置・再発防止策 (CAPA)"
    ],
    [
        "Total Downtime Loss",
        "総停止時間 (ロス)"
    ],
    [
        "Actual / Target",
        "出来高 / 計画数"
    ],
    [
        "Reject / Scrap Qty",
        "不良数 / スクラップ"
    ],
    [
        "World Class Std: 85%",
        "ワールドクラス標準: 85%"
    ],
    [
        "PLANT AVERAGE / TOTALS:",
        "工場平均 / 総合計:"
    ],
    [
        "TOTAL KESELURUHAN DOWNTIME PRODUKSI:",
        "生産停止時間 総合計:"
    ],
    [
        "Overall Plant OEE",
        "工場全体 OEE"
    ],
    [
        "Output vs Reject",
        "生産数 vs 不良数"
    ],
    [
        "Scope Target:",
        "対象範囲:"
    ],
    [
        "Baseline Target OEE:",
        "基準目標 OEE:"
    ],
    [
        "85.00% (World Class)",
        "85.00% (ワールドクラス標準)"
    ],
    [
        "Standard Target Speed:",
        "標準目標速度:"
    ],
    [
        "Ideal Cycle Standard",
        "基準サイクルタイム標準"
    ],
    [
        "Target Quality Rate:",
        "目標良品率:"
    ],
    [
        "99.00% (Zero Defect)",
        "99.00% (不良ゼロ基準)"
    ],
    [
        "Shift Handover Instructions & Next-Shift Action Items",
        "直間引継ぎ指示及び次直連絡事項"
    ],
    [
        "Official MES Handover Protocol",
        "MES公式 直間引継ぎ書"
    ],
    [
        "Diserahkan Oleh (Shift Selesai):",
        "引渡し担当 (現直 職長 / 班長):"
    ],
    [
        "Diterima Oleh (Shift Berikutnya):",
        "受取り担当 (次直 責任者):"
    ],
    [
        "Supervisor Shift In-Charge",
        "現直 責任者 (職長 / 班長)"
    ],
    [
        "Supervisor Incoming Shift",
        "次直 責任者 (引継ぎ者)"
    ],
    [
        "Dibuat Oleh (Operator Produksi):",
        "作成担当 (製造オペレーター):"
    ],
    [
        "Diperiksa Oleh (QC Inspector / Leader):",
        "検査担当 (検査員 / QC班長):"
    ],
    [
        "Disetujui Oleh (Supervisor Produksi / QC):",
        "承認担当 (製造 / QC責任者):"
    ],
    [
        "Dibuat Oleh (Operator / Leader Shift):",
        "起票者 (製造オペレーター / 班長):"
    ],
    [
        "Ditangani Oleh (Teknisi / Maintenance):",
        "保全担当 (工務・設備技術員):"
    ],
    [
        "Mengetahui / Disetujui (Supervisor Produksi):",
        "承認者 (製造管理職):"
    ],
    [
        "Pengesahan Laporan Data NG & Kontrol Kualitas (QC Inspection Approval)",
        "品質検査及び不良実績承認 (QC承認)"
    ],
    [
        "Pengesahan Riwayat Trouble & Kontrol Reliabilitas Mesin (Maintenance Approval)",
        "設備トラブル履歴及び保全・信頼性管理承認 (保全承認)"
    ],
    [
        "LAPORAN DATA NG & KUALITAS DEFECT PRODUKSI",
        "品質不良・NG管理レポート (検査実績日報)"
    ],
    [
        "LAPORAN RIWAYAT TROUBLE & DOWNTIME MESIN PRODUKSI",
        "設備トラブル・停止履歴日報 (保全管理)"
    ],
    [
        "Zero Major Breakdown",
        "重大トラブル発生ゼロ (良好)"
    ],
    [
        "SHIFT COMPLETE",
        "シフト完了"
    ],
    [
        "QC AUDIT REPORT",
        "品質検査報告書"
    ],
    [
        "MAINTENANCE & RELIABILITY AUDIT",
        "保全・信頼性管理"
    ],
    [
        "Laporan Produksi & OEE",
        "1. 生産実績及びOEE日報"
    ],
    [
        "Laporan Produksi",
        "1. 生産実績及びOEE日報"
    ],
    [
        "Laporan Data NG",
        "2. 品質不良・NG管理レポート"
    ],
    [
        "Laporan Trouble & Downtime",
        "3. 設備トラブル・停止履歴日報"
    ],
    [
        "Print Handover (A4)",
        "引継書印刷 (A4)"
    ],
    [
        "Export Excel / CSV",
        "Excel / CSV 出力"
    ],
    [
        "Cetak Laporan NG (A4)",
        "NG日報印刷 (A4)"
    ],
    [
        "Export Excel (Data NG)",
        "NGデータExcel出力"
    ],
    [
        "Cetak Laporan Trouble (A4)",
        "トラブル日報印刷 (A4)"
    ],
    [
        "Export Excel (Data Trouble)",
        "トラブルExcel出力"
    ],
    [
        "Input Laporan Harian",
        "日次生産実績入力"
    ],
    [
        "Input Laporan NG",
        "日次不良品・NG入力"
    ],
    [
        "+ Input Laporan Produksi",
        "+ 生産実績登録"
    ],
    [
        "Finish Good (Units)",
        "完成良品数量 (個)"
    ],
    [
        "Not Good (NG Qty)",
        "不良数量 (NG)"
    ],
    [
        "OEE Rata-Rata Harian",
        "日次平均 OEE"
    ],
    [
        "Total Downtime Harian",
        "日次総停止時間"
    ],
    [
        "Daftar inputan hasil produksi khusus untuk tanggal yang dipilih",
        "選択された日付の製造実績データ一覧"
    ],
    [
        "Production Monitoring",
        "ライン進捗モニター"
    ],
    [
        "TV FLOOR DISPLAY",
        "アンドン・大型モニター表示"
    ],
    [
        "Exit TV Mode",
        "通常モードに戻る"
    ],
    [
        "Downtime Analysis",
        "停止時間・ロス分析"
    ],
    [
        "Total Downtime",
        "総停止時間"
    ],
    [
        "Number of Stops",
        "停止回数"
    ],
    [
        "MTTR (Mean Time To Repair)",
        "平均復旧時間 (MTTR)"
    ],
    [
        "MTBF (Mean Time Between Failures)",
        "平均故障間隔 (MTBF)"
    ],
    [
        "Downtime Reasons Pareto Chart",
        "要因別 停止時間パレート図"
    ],
    [
        "Downtime & Loss Trouble History Logs",
        "停止・トラブル履歴ログ一覧"
    ],
    [
        "Quality Performance",
        "品質・良品率実績分析"
    ],
    [
        "Total Cacat / Reject",
        "総不良品数量 (NG)"
    ],
    [
        "Distribusi Defect per Bagian Komponen",
        "部品別 不良発生比率"
    ],
    [
        "Log Tindakan Korektif & Rekomendasi Kaizen (CAPA)",
        "是正処置及び改善提案ログ (CAPA・カイゼン)"
    ],
    [
        "Distribusi Trouble per Kategori Masalah",
        "要因分類別 トラブル分布"
    ],
    [
        "Top Mesin Terkendala & Rekomendasi CAPA",
        "ワースト停止設備トップ5及び是正提案"
    ],
    [
        "Total Waktu Henti",
        "総停止時間"
    ],
    [
        "Rata-rata Durasi",
        "平均停止時間"
    ],
    [
        "TANGGAL PRODUKSI:",
        "生産日:"
    ],
    [
        "Rekap Produksi",
        "生産実績集計"
    ],
    [
        "Mesin / Lini Operasional",
        "台 稼働中設備"
    ],
    [
        "Total Lines:",
        "対象ライン数:"
    ],
    [
        "Total Defect Pcs",
        "総不良品数量"
    ],
    [
        "Top Defect Reason",
        "最多不良項目"
    ],
    [
        "Target Unplanned Breakdown:",
        "突発停止目標値:"
    ],
    [
        "Total Kejadian Trouble:",
        "総トラブル発生件数:"
    ],
    [
        "Rata-rata Penanganan (MTTR):",
        "平均復旧時間 (MTTR):"
    ],
    [
        "Connecting Rod Body (Wajib OEE)",
        "コネクティングロッド本体 (主要部品)"
    ],
    [
        "Cap Body (Wajib OEE)",
        "キャップ本体 (主要部品)"
    ],
    [
        "Assembly Fitment / Rakitan (Wajib OEE)",
        "アセンブリ結合・組立 (主要工程)"
    ],
    [
        "Komponen Pelengkap (Bolt, Bush, Nut, Pin)",
        "締結・付属部品 (ボルト/ブッシュ/ナット/ピン)"
    ],
    [
        "OEE overview",
        "OEE 総合概要"
    ],
    [
        "Error analysis",
        "異常・エラー分析"
    ],
    [
        "Analysis of sub assets",
        "設備別サブ分析"
    ],
    [
        "Form Input Laporan Produksi & Trouble Log",
        "生産実績及びトラブル記録入力フォーム"
    ],
    [
        "Entry Production & OEE Record",
        "生産実績及びOEE入力"
    ],
    [
        "Tambah Trouble Log & Downtime",
        "トラブル・停止記録の追加"
    ],
    [
        "Detail & Tindakan Masalah Defect (NG)",
        "品質不良 (NG) 詳細及び是正処置"
    ],
    [
        "Detail Rekap Produksi & OEE",
        "生産実績及びOEE詳細"
    ],
    [
        "Detail Laporan Produksi",
        "生産日報詳細"
    ],
    [
        "Detail Laporan Trouble & Downtime",
        "設備トラブル及び停止詳細"
    ],
    [
        "Daftar Trouble Terkait",
        "関連トラブル履歴一覧"
    ],
    [
        "Informasi Rekap Produksi",
        "生産実績集計情報"
    ],
    [
        "Quick Input Produksi Mesin",
        "設備生産クイック入力"
    ],
    [
        "Input Cepat Output & Trouble",
        "出来高・トラブル簡易入力"
    ],
    [
        "Edit Tindakan Penanganan (Countermeasure)",
        "是正処置・対策内容の編集"
    ],
    [
        "Isi tindakan perbaikan untuk mencegah masalah berulang:",
        "再発防止に向けた是正処置内容を入力してください:"
    ],
    [
        "Konfirmasi Pembersihan Data",
        "データ初期化・クリーンアップ確認"
    ],
    [
        "Tindakan ini tidak dapat dibatalkan!",
        "この処理は取り消すことができません！"
    ],
    [
        "Hapus Semua Data Transaksi",
        "全トランザクションデータの削除"
    ],
    [
        "Tanggal Laporan, Line, Mesin, Produk & Shift",
        "1. 生産日・ライン・設備・製品・直"
    ],
    [
        "Hasil Output & Jam Kerja",
        "2. 出来高実績及び稼働時間"
    ],
    [
        "Trouble Log & Loss Time (Opsional)",
        "3. 設備トラブル及び停止ロス (任意)"
    ],
    [
        "Target Qty Otomatis = Planned Time × 60 ÷ Cycle Time",
        "目標数自動計算 = 計画時間 × 60 ÷ サイクルタイム"
    ],
    [
        "Total Losstime: 0 Menit",
        "総損失時間: 0 分"
    ],
    [
        "Total Losstime:",
        "総損失時間:"
    ],
    [
        "+ Tambah Trouble Log",
        "+ トラブル記録を追加"
    ],
    [
        "Simpan & Hitung OEE",
        "保存してOEEを計算"
    ],
    [
        "Save & Calculate OEE",
        "保存してOEEを計算"
    ],
    [
        "Mengerti, Ubah Shift / Line",
        "了解、直またはラインを変更"
    ],
    [
        "Tambah Mesin",
        "新規設備追加"
    ],
    [
        "Edit Mesin",
        "設備編集"
    ],
    [
        "Hapus Mesin",
        "設備削除"
    ],
    [
        "Tambah Line",
        "新規ライン追加"
    ],
    [
        "Edit Line",
        "ライン編集"
    ],
    [
        "Hapus Line",
        "ライン削除"
    ],
    [
        "Tambah Produk",
        "新規製品追加"
    ],
    [
        "Edit Produk",
        "製品編集"
    ],
    [
        "Hapus Produk",
        "製品削除"
    ],
    [
        "Tambah Shift",
        "新規勤務直追加"
    ],
    [
        "Edit Shift",
        "勤務直編集"
    ],
    [
        "Hapus Shift",
        "勤務直削除"
    ],
    [
        "Tambah Downtime Reason",
        "停止要因追加"
    ],
    [
        "Edit Downtime Reason",
        "停止要因編集"
    ],
    [
        "Tambah Defect Reason",
        "不良要因追加"
    ],
    [
        "Edit Defect Reason",
        "不良要因編集"
    ],
    [
        "Tambah Target OEE",
        "OEE目標追加"
    ],
    [
        "Edit Target OEE",
        "OEE目標編集"
    ],
    [
        "Tambah Grup Kerja",
        "作業グループ追加"
    ],
    [
        "Edit Grup Kerja",
        "作業グループ編集"
    ],
    [
        "Tambah Master Data",
        "新規マスター登録"
    ],
    [
        "Master Mesin",
        "設備マスター"
    ],
    [
        "Master Line",
        "生産ラインマスター"
    ],
    [
        "Master Produk",
        "製品・型番マスター"
    ],
    [
        "Master Shift",
        "勤務直マスター"
    ],
    [
        "Master Downtime Reason",
        "停止要因マスター"
    ],
    [
        "Master Defect Reason",
        "不良要因マスター"
    ],
    [
        "Master Target OEE",
        "OEE目標マスター"
    ],
    [
        "Master Grup Kerja",
        "作業グループマスター"
    ],
    [
        "Kode Mesin",
        "設備コード"
    ],
    [
        "Nama Mesin",
        "設備名称"
    ],
    [
        "Line Produksi",
        "生産ライン"
    ],
    [
        "Cycle Time Ideal (detik)",
        "基準サイクルタイム (秒)"
    ],
    [
        "Cycle Time Ideal",
        "基準サイクルタイム"
    ],
    [
        "Status Mesin",
        "設備ステータス"
    ],
    [
        "Kode Line",
        "ラインコード"
    ],
    [
        "Nama Line",
        "ライン名称"
    ],
    [
        "Target Output / Jam",
        "目標出来高 / 時間"
    ],
    [
        "Kode SKU",
        "製品型番 (SKU)"
    ],
    [
        "Nama Produk",
        "製品名称"
    ],
    [
        "Deskripsi Produk",
        "製品仕様・説明"
    ],
    [
        "Nama Shift",
        "直名称"
    ],
    [
        "Durasi Istirahat (menit)",
        "休憩時間 (分)"
    ],
    [
        "Kategori Reason",
        "要因分類"
    ],
    [
        "Kode Reason",
        "要因コード"
    ],
    [
        "Nama Reason",
        "要因名称"
    ],
    [
        "Import Data Excel / CSV",
        "Excel / CSV データインポート"
    ],
    [
        "Import Data Excel",
        "Excelデータインポート"
    ],
    [
        "Pilih File Spreadsheet (xlsx, csv)",
        "ファイル選択 (xlsx, csv)"
    ],
    [
        "Download Format Template",
        "テンプレート書式ダウンロード"
    ],
    [
        "Mulai Proses Import",
        "インポート実行"
    ],
    [
        "Simpan Perubahan & Hitung Ulang OEE",
        "変更を保存してOEE再計算"
    ],
    [
        "Hitung Ulang OEE",
        "OEE再計算"
    ],
    [
        "Simpan Perubahan Data",
        "変更データを保存"
    ],
    [
        "Simpan Perubahan",
        "変更を保存"
    ],
    [
        "Simpan Data",
        "データを保存"
    ],
    [
        "Edit Record",
        "データ編集"
    ],
    [
        "Cetak Slip Produksi",
        "製造伝票印刷"
    ],
    [
        "Hapus Record",
        "レコード削除"
    ],
    [
        "Tambah Akun Pengguna Baru (RBAC)",
        "新規ユーザーアカウント追加 (RBAC)"
    ],
    [
        "Tambah User Baru",
        "新規ユーザー追加"
    ],
    [
        "Edit Data Akun Pengguna",
        "ユーザーアカウント情報編集"
    ],
    [
        "Edit Data User",
        "ユーザー情報編集"
    ],
    [
        "Hapus Akun Pengguna",
        "ユーザーアカウント削除"
    ],
    [
        "Hapus Data User",
        "ユーザー削除"
    ],
    [
        "Nama Lengkap Pengguna",
        "ユーザー氏名 (フルネーム)"
    ],
    [
        "Nama Lengkap",
        "氏名 (フルネーム)"
    ],
    [
        "Corporate Email / Username",
        "社用メール / ユーザー名"
    ],
    [
        "Username / Akun",
        "ユーザー名 / アカウント"
    ],
    [
        "Password (Default: password)",
        "パスワード (初期値: password)"
    ],
    [
        "Konfirmasi Password",
        "パスワード確認"
    ],
    [
        "Role & Hak Akses (RBAC)",
        "権限・役割 (Role RBAC)"
    ],
    [
        "Role / Hak Akses",
        "権限・役割 (Role)"
    ],
    [
        "Status Akun Aktif (Active)",
        "有効ステータス (Active)"
    ],
    [
        "Simpan Akun",
        "アカウント登録"
    ],
    [
        "Super Admin",
        "システム管理者 (Super Admin)"
    ],
    [
        "Production Planner",
        "生産計画担当 (Planner)"
    ],
    [
        "Line Supervisor",
        "現場監督者 (Supervisor)"
    ],
    [
        "Operator / Teknisi",
        "オペレーター / 保全技術員"
    ],
    [
        "Quality Inspector",
        "品質検査員 (QC Inspector)"
    ],
    [
        "Manajemen Pengguna",
        "ユーザー管理"
    ],
    [
        "Daftar Akun Pengguna Sistem",
        "システム登録ユーザー一覧"
    ],
    [
        "Hapus Catatan Produksi",
        "生産実績レコード削除"
    ],
    [
        "Tindakan ini akan menghapus permanen data laporan & OEE terkait.",
        "この操作により関連する製造日報及びOEEデータが完全に削除されます。"
    ],
    [
        "Apakah Anda yakin ingin menghapus data laporan produksi",
        "以下の製造日報データを削除してもよろしいですか"
    ],
    [
        "Ya, Hapus Record",
        "はい、レコードを削除します"
    ],
    [
        "Hapus Log Trouble",
        "設備トラブルログ削除"
    ],
    [
        "Tindakan ini akan menghapus log kendala downtime dari riwayat.",
        "この操作により停止履歴から該当トラブルログが削除されます。"
    ],
    [
        "Apakah Anda yakin ingin menghapus",
        "本当に削除してもよろしいですか"
    ],
    [
        "Ya, Hapus Log",
        "はい、ログを削除します"
    ],
    [
        "Delete Record",
        "レコード削除"
    ],
    [
        "This action will remove the record from master data.",
        "この操作によりマスターデータからレコードが削除されます。"
    ],
    [
        "Are you sure you want to delete",
        "本当に削除してもよろしいですか"
    ],
    [
        "Konfirmasi Pembersihan Data Go-Live",
        "本番稼働用データ初期化・クリーンアップ確認"
    ],
    [
        "Tindakan ini memerlukan verifikasi frasa keamanan.",
        "この処理を実行するにはセキュリティ確認フレーズの入力が必要です。"
    ],
    [
        "Target Cakupan Pembersihan:",
        "初期化対象範囲:"
    ],
    [
        "Jaminan Keamanan Master Data",
        "マスターデータ保護の保証"
    ],
    [
        "TIDAK AKAN DIHAPUS",
        "は削除されません (保持されます)"
    ],
    [
        "Edit Rekomendasi Tindakan Korektif",
        "是正処置・改善推奨事項の編集"
    ],
    [
        "Catatan Kaizen:",
        "カイゼン注記:"
    ],
    [
        "Rekomendasi Tindakan Korektif (Countermeasure / Kaizen SOP)",
        "是正処置・カイゼン標準手順 (CAPA / Kaizen SOP)"
    ],
    [
        "Simpan Rekomendasi",
        "是正推奨処置を保存"
    ],
    [
        "Log Entry Record:",
        "生産実績クイック入力:"
    ],
    [
        "Finish Good Quantity (Pcs)",
        "完成良品数量 (個)"
    ],
    [
        "Not Good (NG) Quantity (Pcs)",
        "不良数量 (NG) (個)"
    ],
    [
        "Target Qty (Pcs)",
        "目標生産数 (個)"
    ],
    [
        "Submit Log Entry",
        "実績データを登録"
    ],
    [
        "Tanggal Produksi",
        "生産日"
    ],
    [
        "Production Line",
        "生産ライン"
    ],
    [
        "Mesin Input",
        "対象設備"
    ],
    [
        "Nama Produk",
        "製品名・型番"
    ],
    [
        "Shift Kerja",
        "勤務直 (シフト)"
    ],
    [
        "Target Qty",
        "目標生産数"
    ],
    [
        "Total Measuring",
        "実測数量"
    ],
    [
        "Finish Good",
        "完成良品数量"
    ],
    [
        "Not Good (NG)",
        "不良数量 (NG)"
    ],
    [
        "Scrap / Afkir",
        "廃棄スクラップ"
    ],
    [
        "Planned Time (min)",
        "計画時間 (分)"
    ],
    [
        "Planned Downtime",
        "計画停止 (分)"
    ],
    [
        "Run Time (min)",
        "実稼働時間 (分)"
    ],
    [
        "Jam Mulai (Start)",
        "開始時刻"
    ],
    [
        "Jam Selesai (End)",
        "終了時刻"
    ],
    [
        "Jam Mulai",
        "開始時刻"
    ],
    [
        "Jam Selesai",
        "終了時刻"
    ],
    [
        "Durasi (Menit)",
        "停止時間 (分)"
    ],
    [
        "Kategori Masalah",
        "トラブル分類"
    ],
    [
        "Penyebab Masalah (Root Cause)",
        "発生原因 (RCA)"
    ],
    [
        "Tindakan Perbaikan (CAPA)",
        "是正処置 (CAPA)"
    ],
    [
        "PIC / Teknisi",
        "担当者 / 保全"
    ],
    [
        "Hapus Baris",
        "行削除"
    ],
    [
        "Input Detail Masalah NG",
        "不良内容の詳細入力"
    ],
    [
        "Simpan Detail Masalah",
        "不良詳細を保存"
    ],
    [
        "Tutup",
        "閉じる"
    ],
    [
        "Close",
        "閉じる"
    ],
    [
        "Batal",
        "キャンセル"
    ],
    [
        "Cancel",
        "キャンセル"
    ],
    [
        "Data Tidak Dapat Disimpan",
        "データを保存できません"
    ],
    [
        "Periksa Isian Form Data",
        "入力内容の確認"
    ],
    [
        "Periksa Kembali Isian Form Produksi",
        "生産フォームの入力確認"
    ],
    [
        "Harap lengkapi isian data yang masih kosong berikut:",
        "以下の必須項目を入力または確認してください:"
    ],
    [
        "Ditemukan beberapa isian yang masih kosong atau tidak valid:",
        "未入力または不正な項目があります:"
    ],
    [
        "Perbaiki Isian Data Sekarang",
        "入力を今すぐ修正する"
    ],
    [
        "Perbaiki Isian Data",
        "入力内容を確認・修正する"
    ],
    [
        "Konfirmasi Tindakan",
        "実行確認"
    ],
    [
        "Apakah Anda yakin ingin melanjutkan tindakan ini?",
        "この処理を実行してもよろしいですか？"
    ],
    [
        "Ya, Lanjutkan",
        "はい、実行します"
    ],
    [
        "Data sudah ditambahkan tidak boleh sama !",
        "重複エラー: 登録済みデータと重複しています！"
    ],
    [
        "⚠️ Gagal Menyimpan",
        "⚠️ 保存に失敗しました"
    ],
    [
        "Data Berhasil Disimpan",
        "データを正常に保存しました"
    ],
    [
        "Data Berhasil Diperbarui",
        "データを正常に更新しました"
    ],
    [
        "Data Berhasil Dihapus",
        "データを正常に削除しました"
    ],
    [
        "Pilih Production Line terlebih dahulu",
        "生産ラインを選択してください"
    ],
    [
        "Pilih Mesin terlebih dahulu",
        "設備を選択してください"
    ],
    [
        "Pilih Produk terlebih dahulu",
        "製品を選択してください"
    ],
    [
        "Pilih Shift terlebih dahulu",
        "直 (シフト) を選択してください"
    ],
    [
        "Target output harus lebih dari 0",
        "目標生産数は0より大きい値を入力してください"
    ],
    [
        "Total Measuring Qty (Input Output Aktual) harus lebih dari 0",
        "総測定数は0より大きい値を入力してください"
    ],
    [
        "Good Qty (Finish Good) tidak boleh melebihi Total Measuring Qty",
        "良品数は総測定数を超えることはできません"
    ],
    [
        "Total jam kerja shift (Planned Time) harus lebih dari 0",
        "計画稼働時間は0より大きい値を入力してください"
    ],
    [
        "Total jam berhenti (Planned Downtime) tidak boleh melebihi Total Jam Kerja Shift",
        "計画停止時間は計画総稼働時間を超えることはできません"
    ],
    [
        "Jam Start Trouble: Wajib diisi jika terdapat catatan trouble kendala mesin.",
        "トラブル開始時刻: 設備トラブル記録時は開始時刻の入力が必須です。"
    ],
    [
        "Jam Start Trouble:",
        "トラブル開始時刻:"
    ],
    [
        "Durasi Trouble:",
        "トラブル停止時間:"
    ],
    [
        "Durasi kendala",
        "トラブル停止時間"
    ],
    [
        "tidak boleh melebihi total jam kerja shift",
        "はシフト総稼働時間を超えることはできません"
    ],
    [
        "Wajib diisi jika terdapat catatan trouble kendala mesin.",
        "設備トラブル記録時は開始時刻の入力が必須です。"
    ],
    [
        "Kombinasi Line dan Shift pada tanggal tersebut sudah pernah ditambahkan ke sistem.",
        "該当日のラインおよび直の組み合わせは既にシステムに登録されています。"
    ],
    [
        "Minimal harus mengisi 1 baris rincian komponen NG.",
        "最低1行以上のNG部品内訳を入力してください。"
    ],
    [
        "Saya Mengerti",
        "了解しました"
    ],
    [
        "Komponen OEE wajib dipilih.",
        "主要部品の選択は必須です。"
    ],
    [
        "Jumlah Qty NG tidak boleh 0 atau kosong.",
        "NG数量は0または空にできません。"
    ],
    [
        "Bagian NG wajib dipilih.",
        "NG発生部位の選択は必須です。"
    ],
    [
        "Penyebab / Remark defect wajib dipilih.",
        "不良要因・備考の選択は必須です。"
    ],
    [
        "Perbaiki Isian Sekarang",
        "入力を今すぐ修正する"
    ],
    [
        "Isi data detail yang masih kosong! (Komponen, Jumlah, Bagian NG, Penyebab tidak boleh kosong)",
        "未入力の項目があります（部品、数量、部位、要因は入力必須です）"
    ],
    [
        "Reset & Hapus Detail NG",
        "NG詳細のリセット及び削除"
    ],
    [
        "Apakah Anda yakin ingin menghapus / mereset rincian detail NG untuk laporan ini?",
        "このレポートのNG詳細内訳をリセット・削除してもよろしいですか？"
    ],
    [
        "Ya, Hapus Data",
        "はい、データを削除します"
    ],
    [
        "Detail NG berhasil direset.",
        "NG詳細データが正常にリセットされました。"
    ],
    [
        "Data Dihapus",
        "データ削除完了"
    ],
    [
        "Gagal Menghapus",
        "削除に失敗しました"
    ],
    [
        "Deskripsi Gejala Trouble",
        "トラブル症状・現象の詳細"
    ],
    [
        "Tindakan Perbaikan (Countermeasure)",
        "是正処置・応急処置 (対策)"
    ],
    [
        "Detail kendala yang terjadi...",
        "発生したトラブルの詳細..."
    ],
    [
        "Langkah perbaikan yang telah dilakukan...",
        "実施した復旧・是正処置..."
    ],
    [
        "Komponen OEE",
        "主要部品"
    ],
    [
        "Jumlah (Pcs)",
        "数量 (個)"
    ],
    [
        "Bagian NG (Ketik / Cari)",
        "不良部位 (検索 / 選択)"
    ],
    [
        "Penyebab / Remark (Ketik / Cari)",
        "不良要因 / 備考 (検索 / 選択)"
    ],
    [
        "Catatan / Keterangan Tambahan",
        "特記事項・補足メモ"
    ],
    [
        "Nama Inspector QC",
        "検査担当者名 (QC)"
    ],
    [
        "Catatan analisa akar masalah atau tindakan penanganan...",
        "要因分析メモまたは処置内容..."
    ],
    [
        "Simpan Detail Laporan NG",
        "NG詳細レポートを保存"
    ],
    [
        "Simpan Laporan Produksi",
        "生産日報を保存"
    ],
    [
        "Reset / Hapus Detail",
        "詳細をリセット / 削除"
    ],
    [
        "Lihat Detail",
        "詳細表示"
    ],
    [
        "Cari Data",
        "データ検索"
    ],
    [
        "Filter Data",
        "データ絞り込み"
    ],
    [
        "Terapkan Filter",
        "条件適用"
    ],
    [
        "Clear Filters",
        "条件クリア"
    ],
    [
        "Reset Filter",
        "条件解除"
    ],
    [
        "Hari Ini",
        "本日"
    ],
    [
        "Semua Data",
        "すべてのデータ"
    ],
    [
        "Total Data:",
        "総件数:"
    ],
    [
        "Baris per halaman",
        "表示件数"
    ],
    [
        "Sebelumnya",
        "前へ"
    ],
    [
        "Berikutnya",
        "次へ"
    ],
    [
        "Aksi",
        "操作"
    ],
    [
        "Status",
        "状態"
    ],
    [
        "Keterangan",
        "備考"
    ],
    [
        "Catatan",
        "特記メモ"
    ],
    [
        "Tidak ada data",
        "データがありません"
    ],
    [
        "Memuat data...",
        "データを読み込み中..."
    ],
    [
        "Loading...",
        "読み込み中..."
    ],
    [
        "(SEIMBANG ✓)",
        "(一致 ✓)"
    ],
    [
        "SEIMBANG",
        "一致"
    ],
    [
        "Kurang",
        "不足"
    ],
    [
        "Lebih",
        "超過"
    ],
    [
        "Total:",
        "合計:"
    ],
    [
        "Minimal harus memiliki 1 baris rincian NG.",
        "NG明細行は最低1行必要です。"
    ],
    [
        "Detail NG berhasil direset.",
        "NG明細が正常にリセットされました。"
    ],
    [
        "Detail NG Tersimpan",
        "NG詳細レポート保存完了"
    ],
    [
        "Catatan Produksi Dihapus",
        "生産実績レコード削除完了"
    ],
    [
        "Log Trouble Dihapus",
        "トラブルログ削除完了"
    ],
    [
        "Log Trouble Diperbarui",
        "トラブルログ更新完了"
    ],
    [
        "Record Produksi Diperbarui",
        "生産実績更新完了"
    ],
    [
        "Gagal menghapus catatan produksi",
        "生産実績の削除に失敗しました"
    ],
    [
        "Gagal menghapus log trouble",
        "トラブルログの削除に失敗しました"
    ],
    [
        "Gagal menyimpan",
        "保存に失敗しました"
    ],
    [
        "Gagal membuat user",
        "ユーザー作成に失敗しました"
    ],
    [
        "Gagal mengupdate user",
        "ユーザー更新に失敗しました"
    ],
    [
        "Gagal memuat data",
        "データの読み込みに失敗しました"
    ],
    [
        "Template Excel Siap",
        "Excelテンプレートの準備完了"
    ],
    [
        "Excel Export Berhasil",
        "Excelエクスポート完了"
    ],
    [
        "Profil Perusahaan Tersimpan",
        "会社基本情報が保存されました"
    ],
    [
        "Database Dioptimasi",
        "データベース最適化が完了しました"
    ],
    [
        "Ekspor Backup Dimulai",
        "バックアップのエクスポートを開始しました"
    ],
    [
        "Memperbarui Status",
        "ステータス更新中"
    ],
    [
        "data laporan produksi",
        "生産日報データ"
    ],
    [
        "laporan produksi",
        "生産日報"
    ],
    [
        "catatan produksi",
        "生産実績"
    ],
    [
        "log trouble",
        "トラブルログ"
    ],
    [
        "kendala downtime",
        "停止ロス"
    ],
    [
        "dari riwayat",
        "履歴から"
    ],
    [
        "dari database",
        "データベースから"
    ],
    [
        "dari sistem",
        "システムから"
    ],
    [
        "berhasil dihapus dari database!",
        "データベースから正常に削除されました！"
    ],
    [
        "berhasil dihapus dari sistem.",
        "システムから正常に削除されました。"
    ],
    [
        "berhasil didaftarkan!",
        "正常に登録されました！"
    ],
    [
        "berhasil diperbarui!",
        "正常に更新されました！"
    ],
    [
        "Antrean Laporan NG",
        "NG報告受付・入力待ち"
    ],
    [
        "Status Input Detail",
        "入力進捗ステータス"
    ],
    [
        "Total Target NG:",
        "目標NG数合計:"
    ],
    [
        "Total Target NG",
        "目標NG数合計"
    ],
    [
        "Sudah Lengkap:",
        "入力完了:"
    ],
    [
        "Sudah Lengkap",
        "入力完了"
    ],
    [
        "Komponen Wajib (OEE)",
        "主要部品 (OEE対象)"
    ],
    [
        "Pelengkap (Non-OEE)",
        "付属部品 (非OEE)"
    ],
    [
        "Pelengkap (non-OEE)",
        "付属部品 (非OEE)"
    ],
    [
        "non-OEE",
        "非OEE"
    ],
    [
        "Pcs non-OEE",
        "個 (非OEE)"
    ],
    [
        "(20 Pcs non-OEE)",
        "(20個 非OEE)"
    ],
    [
        "Daftar Antrean Laporan Harian yang Memiliki Reject / Defect",
        "品質不良・NG発生日報一覧 (要内訳入力)"
    ],
    [
        "Hanya menampilkan transaksi harian dengan Total NG > 0 yang memerlukan rincian komponen",
        "内訳登録が必要な不良数(Total NG > 0)の生産実績のみを表示しています"
    ],
    [
        "Hanya menampilkan transaksi harian dengan Total NG &gt; 0 yang memerlukan rincian komponen",
        "内訳登録が必要な不良数(Total NG > 0)の生産実績のみを表示しています"
    ],
    [
        "Cari Lini, Mesin, SKU, Produk...",
        "ライン、設備、品番(SKU)、製品名で検索..."
    ],
    [
        "Total Terdata:",
        "登録済合計:"
    ],
    [
        "Total Terdata",
        "登録済合計"
    ],
    [
        "Belum dirinci",
        "未登録"
    ],
    [
        "Belum diisi",
        "未入力"
    ],
    [
        "Belum Diisi",
        "未入力"
    ],
    [
        "Perlu Input",
        "要入力"
    ],
    [
        "Input Detail",
        "内訳入力"
    ],
    [
        "Edit Detail",
        "内訳編集"
    ],
    [
        "Lengkap",
        "登録完了"
    ],
    [
        "Selisih",
        "差異"
    ],
    [
        "Semua Status",
        "全ステータス"
    ],
    [
        "Selesai Terisi",
        "入力完了"
    ],
    [
        "Tidak ada antrean laporan NG",
        "NG報告の待機データはありません"
    ],
    [
        "Semua laporan harian pada filter ini memiliki 0 reject atau detail NG telah selesai diinput.",
        "対象期間の全日報で不良ゼロ、またはNG内訳の登録がすべて完了しています。"
    ],
    [
        "Semua Lini Produksi",
        "全生産ライン"
    ],
    [
        "Semua Shift",
        "全直 (シフト)"
    ],
    [
        "Rincian Komponen Wajib (OEE)",
        "主要部品内訳 (OEE)"
    ],
    [
        "Status Input",
        "入力進捗状態"
    ],
    [
        "2 Laporan",
        "2件"
    ],
    [
        "1 Laporan",
        "1件"
    ],
    [
        "1 Pending",
        "1件 未入力"
    ],
    [
        "Pending",
        "未入力"
    ],
    [
        "Tanggal & Shift",
        "生産日・直"
    ],
    [
        "Lini & Mesin",
        "ライン・設備"
    ],
    [
        "Produk - SKU",
        "製品名・型番 (SKU)"
    ],
    [
        "Total Output",
        "総生産数"
    ],
    [
        "Target NG",
        "目標NG数"
    ],
    [
        "SLIP LAPORAN HASIL PRODUKSI",
        "生産実績及びOEE設備管理票 (製造スリップ)"
    ],
    [
        "Slip Laporan Harian Tgl",
        "日次生産実績スリップ 日付"
    ],
    [
        "Standard Form: A4-Portrait Production Slip",
        "標準帳票: A4縦型 製造管理スリップ"
    ],
    [
        "I. HASIL PENCAPAIAN OUTPUT PRODUKSI & ANALISIS MUTU",
        "I. 生産実績高及び品質分析"
    ],
    [
        "II. STRUKTUR WAKTU KERJA & LOSS AVAILABILITY",
        "II. 操業時間構造及び時間稼働ロス"
    ],
    [
        "I. RINGKASAN REKAPITULASI KUALITAS & DEFECT (QC SUMMARY)",
        "I. 品質実績・不良集計サマリー (QCサマリー)"
    ],
    [
        "I. RINGKASAN REKAPITULASI KUALITAS",
        "I. 品質実績・不良集計サマリー"
    ],
    [
        "II. ANALISIS DISTRIBUSI DEFECT PER KOMPONEN & DEFECT CATEGORY",
        "II. 部品別・要因別不良分布パレート分析"
    ],
    [
        "III. LOG DATA DETAIL PEMERIKSAAN KUALITAS & TINDAKAN DISPOSISI",
        "III. 品質検査詳細ログ及び処置・是正記録"
    ],
    [
        "I. RINGKASAN EKSEKUTIF WAKTU HENTI (DOWNTIME & RELIABILITY KPI)",
        "I. 設備停止・信頼性実績サマリー (DOWNTIME & RELIABILITY KPI)"
    ],
    [
        "II. BREAKDOWN KATEGORI STOP & ANALISIS PARETO MESIN TERKENDALA",
        "II. 停止要因別分類及びワースト設備パレート分析"
    ],
    [
        "III. LOG DETAIL KEJADIAN TROUBLE, ROOT CAUSE & TINDAKAN KOREKTIF (CAPA)",
        "III. 設備トラブル詳細記録・根本原因及び是正処置 (5-Why CAPA)"
    ],
    [
        "I. RINGKASAN EKSEKUTIF & PENCAPAIAN 3 ELEMEN OEE",
        "I. 設備総合効率 (OEE) 3要素実績サマリー"
    ],
    [
        "II. DETAIL PRODUKSI & LOSS HARIAN",
        "II. 日次生産実績及びロス内訳"
    ],
    [
        "III. PARETO KEHILANGAN WAKTU & TOP 5 DOWNTIME",
        "III. 停止時間ロスパレート及びワースト5トラブル"
    ],
    [
        "IV. DISTRIBUSI CACAT MUTU & SCRAP",
        "IV. 品質不良分布及びスクラップ内訳"
    ],
    [
        "V. LOG CATATAN TROUBLE & ROOT CAUSE ACTION (CAPA)",
        "V. 設備トラブル履歴及び要因・是正処置 (CAPA)"
    ],
    [
        "VI. LEMBAR PENGESAHAN LAPORAN (SERAH TERIMA SHIFT)",
        "VI. 承認・検印欄 (直間引継ぎ書)"
    ],
    [
        "Parameter Mutu & Output",
        "品質及び生産高パラメータ"
    ],
    [
        "Rasio / Standar",
        "比率 / 基準"
    ],
    [
        "Status Verifikasi",
        "検証ステータス"
    ],
    [
        "Standard Rencana Shift",
        "直計画基準"
    ],
    [
        "Output Kotor Counter",
        "カウンター総出来高"
    ],
    [
        "TERPENUHI",
        "達成 (OK)"
    ],
    [
        "DI BAWAH TARGET",
        "未達 (BELOW TARGET)"
    ],
    [
        "PASSED (OK)",
        "合格 (OK)"
    ],
    [
        "DEFECT DETECTED",
        "不良検出"
    ],
    [
        "ZERO DEFECT",
        "不良ゼロ (ZERO DEFECT)"
    ],
    [
        "Kerugian Material",
        "材料ロス"
    ],
    [
        "DISCARDED",
        "廃棄処置"
    ],
    [
        "Standar Kecepatan Mesin",
        "設備基準タクト"
    ],
    [
        "STANDARD SPEED",
        "基準速度"
    ],
    [
        "Kategori Alokasi Waktu",
        "時間配分区分"
    ],
    [
        "Durasi (Menit)",
        "時間 (分)"
    ],
    [
        "Durasi (Jam)",
        "時間 (時間)"
    ],
    [
        "Total Jadwal Shift",
        "総シフト予定時間"
    ],
    [
        "Planned Break",
        "計画休憩"
    ],
    [
        "PRODUKTIF",
        "稼働中 (PROD)"
    ],
    [
        "DOWNTIME LOSS",
        "停止ロス"
    ],
    [
        "ZERO BREAKDOWN",
        "突発停止ゼロ"
    ],
    [
        "Catatan Kendala Operasional:",
        "操業トラブル特記事項:"
    ],
    [
        "Terdapat kehilangan ketersediaan mesin (*Unplanned Downtime*) sebesar",
        "設備停止ロス (突発停止時間) が"
    ],
    [
        "menit. Pastikan tindakan korektif (CAPA) telah diverifikasi pada log trouble mesin.",
        "分 発生しました。設備トラブルログにて是正処置 (CAPA) が完了していることを確認してください。"
    ],
    [
        "Dibuat Oleh (Operator / Leader):",
        "作成 (作業者 / 班長):"
    ],
    [
        "Diperiksa Oleh (QC Inspector):",
        "確認 (検査担当 / QC):"
    ],
    [
        "Mengetahui / Disetujui:",
        "承認 (職長 / 管理者):"
    ],
    [
        "Operator / Shift Leader",
        "作業者 / 班長"
    ],
    [
        "Quality Assurance & Control",
        "品質保証 / 検査"
    ],
    [
        "Supervisor / Dept Head",
        "職長 / 製造部長"
    ],
    [
        "Shift Name",
        "直名称"
    ],
    [
        "OEE %",
        "OEE効率 (%)"
    ],
    [
        "Penyebab Defect / Remark",
        "不良要因・備考"
    ],
    [
        "Bagian NG (Section)",
        "不良発生部位"
    ],
    [
        "Reject Qty",
        "不良数 (NG)"
    ],
    [
        "Kumulatif (%)",
        "累積比率 (%)"
    ],
    [
        "Klasifikasi",
        "区分・分類"
    ],
    [
        "Line ID & Name",
        "ラインID・名称"
    ],
    [
        "Machine & Produk - SKU",
        "設備・製品型番 (SKU)"
    ],
    [
        "Planned (Min)",
        "計画稼働 (分)"
    ],
    [
        "Down (Min)",
        "停止時間 (分)"
    ],
    [
        "Avail (%)",
        "時間稼働率 (%)"
    ],
    [
        "Target Speed",
        "基準速度"
    ],
    [
        "Output (Pcs)",
        "出来高 (個)"
    ],
    [
        "Perf (%)",
        "性能稼働率 (%)"
    ],
    [
        "Defect (Rej+Scrap)",
        "不良数 (NG+損)"
    ],
    [
        "Quality (%)",
        "良品率 (%)"
    ],
    [
        "Type Produk (SKU)",
        "製品型番 (SKU)"
    ],
    [
        "OK (Pcs)",
        "良品数 (OK)"
    ],
    [
        "Total NG",
        "総不良数"
    ],
    [
        "Defect %",
        "不良率 (%)"
    ],
    [
        "Wajib OEE (Assy/Rod/Cap)",
        "主要部品 (Assy/Rod/Cap)"
    ],
    [
        "Pelengkap (Bolt/Bush/Nut/Pin)",
        "締結部品 (Bolt/Bush/Nut/Pin)"
    ],
    [
        "Kategori & Masalah Defect",
        "不良分類・現象"
    ],
    [
        "Tanggal & Waktu",
        "発生日時"
    ],
    [
        "Gejala & Root Cause Trouble",
        "異常現象及び根本要因"
    ],
    [
        "PIC / Tim",
        "担当 / 班"
    ],
    [
        "Parameter Metrik",
        "評価指標パラメータ"
    ],
    [
        "Target Standar",
        "基準目標値"
    ],
    [
        "Pencapaian Aktual",
        "実績値"
    ],
    [
        "Deviasi",
        "差異・乖離"
    ],
    [
        "Kategori Six Big Loss",
        "6大ロス分類"
    ],
    [
        "Pilar OEE Terkait",
        "関連OEE要素"
    ],
    [
        "Durasi (Jam)",
        "停止時間 (時間)"
    ],
    [
        "% Kontribusi Loss",
        "ロス比率 (%)"
    ],
    [
        "ID Incident",
        "インシデントID"
    ],
    [
        "Jam Trouble",
        "発生時刻"
    ],
    [
        "Mesin & Line",
        "設備・ライン"
    ],
    [
        "Durasi (m)",
        "停止 (分)"
    ],
    [
        "Akar Masalah (5 Whys RCA)",
        "根本原因 (5なぜ分析)"
    ],
    [
        "Total Output Produksi",
        "総生産実績数"
    ],
    [
        "Good Output (OK)",
        "良品数量 (OK)"
    ],
    [
        "Total Reject / NG",
        "総不良数量 (NG)"
    ],
    [
        "Defect Rate (%)",
        "不良率 (%)"
    ],
    [
        "Quality Yield Rate (%)",
        "良品歩留まり率 (%)"
    ],
    [
        "Wajib OEE (A/R/C)",
        "主要部品 (Assy/Rod/Cap)"
    ],
    [
        "Pelengkap (B/Bs/N/P)",
        "締結部品 (Bolt/Bush/Nut/Pin)"
    ],
    [
        "Masalah / Kategori Defect",
        "不良分類・現象"
    ],
    [
        "Tindakan CAPA",
        "是正処置 (CAPA)"
    ],
    [
        "Plan (m)",
        "計画 (分)"
    ],
    [
        "Down (m)",
        "停止 (分)"
    ],
    [
        "Speed (p/m)",
        "速度 (個/分)"
    ],
    [
        "Defect (Pcs)",
        "不良 (個)"
    ],
    [
        "Qual (%)",
        "品質 (%)"
    ],
    [
        "Wajib OEE (Komponen Utama)",
        "主要部品 (OEE対象)"
    ],
    [
        "Pelengkap Non-OEE",
        "付属部品 (非OEE)"
    ],
    [
        "Analisis Gangguan & Downtime Mesin",
        "設備トラブル及び停止ロス分析"
    ],
    [
        "Analisis Cacat & Defect Produk (NG)",
        "品質不良及びNG分析"
    ],
    [
        "Peringkat Efisiensi Sub-Asset Mesin (Machine Ranking)",
        "設備別 総合効率ランキング (Machine Ranking)"
    ],
    [
        "Machine Performance",
        "設備別稼働実績"
    ],
    [
        "List Data Produksi — Tanggal",
        "生産実績データ一覧 — 生産日"
    ],
    [
        "Log Trouble & Downtime — Tanggal",
        "トラブル・停止ログ一覧 — 生産日"
    ],
    [
        "Komponen Utama (Wajib OEE) — Rincian Multi-Baris",
        "主要構成部品 (OEE対象) — 明細登録"
    ],
    [
        "Komponen Pelengkap & Fasteners (Opsional)",
        "付属・締結部品 (非OEE・任意)"
    ],
    [
        "Analisa Defect & Disposisi QC",
        "不良要因分析及びQC処置判定"
    ],
    [
        "Shift Performance",
        "直別稼働実績 (シフト比較)"
    ],
    [
        "Shift OEE & Pillars Comparative Metrics",
        "直別OEE及び3要素比較指標"
    ],
    [
        "Diagram Pareto Penyebab Reject (Vital Few vs Useful Many)",
        "不良原因パレート図 (重点項目 vs その他)"
    ],
    [
        "Proporsi Reject per Komponen",
        "部品別 不良発生比率"
    ],
    [
        "Ranking Bagian NG Terbanyak (Defect Section Breakdown)",
        "ワースト不良部位ランキング"
    ],
    [
        "Matriks Top 10 Defect Reason & Rekomendasi Solusi Teknis",
        "ワースト10不良要因マトリクス及び技術対策提案"
    ],
    [
        "Gagal Memuat Analisis Pareto",
        "パレート分析データの読み込みに失敗しました"
    ],
    [
        "Reports & Export",
        "帳票・実績日報出力"
    ],
    [
        "Tabel Rekapitulasi Rincian Data Defect & NG",
        "品質不良・NG集計実績テーブル"
    ],
    [
        "Tabel Rekapitulasi Rincian Data Trouble & Downtime Mesin",
        "設備トラブル・停止履歴集計テーブル"
    ],
    [
        "Speed & Output Telemetry",
        "速度・出来高テレメトリ"
    ],
    [
        "Recorded Stoppages & Downtimes",
        "記録された設備停止・ダウンタイム"
    ],
    [
        "Global OEE Target Thresholds",
        "グローバルOEE目標閾値設定"
    ],
    [
        "Production Floor TV Mode Settings",
        "現場大型TVモニター表示設定"
    ],
    [
        "Preview Kop Laporan",
        "帳票レターヘッドプレビュー"
    ],
    [
        "Upload Logo Perusahaan",
        "企業ロゴアップロード"
    ],
    [
        "Informasi Legal & Operasional Pabrik",
        "工場基本情報及びレターヘッド設定"
    ],
    [
        "Standar Baseline Target OEE (World Class TPM Metrics)",
        "基準目標OEE標準 (ワールドクラスTPM基準)"
    ],
    [
        "Preferensi Antarmuka & Interval Auto-Refresh",
        "表示設定及び自動更新インターバル"
    ],
    [
        "Gagal Mengambil Diagnostik Database",
        "データベース診断情報の取得に失敗しました"
    ],
    [
        "Manajemen Database & Pembersihan Go-Live",
        "データベース管理及び本番稼働用初期化"
    ],
    [
        "Pembersihan Data Massal untuk Persiapan Live (Go-Live Preparation)",
        "本番稼働に向けたテストデータの一括初期化"
    ],
    [
        "Pembersihan Total Transaksi",
        "全トランザクションデータ初期化"
    ],
    [
        "Tabel Data Transaksional",
        "トランザクションデータテーブル"
    ],
    [
        "Tabel Master & Konfigurasi",
        "マスタ及び設定テーブル"
    ],
    [
        "Total downtime & speed loss across 6 TPM pillars",
        "TPM 6大ロスに基づく停止時間及び速度低下集計"
    ],
    [
        "Top penyebab waktu henti mesin (Availability Losses)",
        "ワースト設備停止要因 (時間稼働ロス)"
    ],
    [
        "Top jenis cacat & Rekomendasi Tindakan Korektif",
        "ワースト不良項目及び是正処置提案"
    ],
    [
        "Ringkasan efisiensi dan output per lini produksi",
        "ライン別 OEE効率及び出来高サマリー"
    ],
    [
        "Daftar utilisasi seluruh unit mesin dan stasiun kerja",
        "全設備・ワークステーション稼働状況一覧"
    ],
    [
        "Peringkat efisiensi OEE, ketersediaan mesin, kualitas, dan downtime per unit sub-asset.",
        "設備ごとのOEE効率、時間稼働率、良品率、停止時間ランキング"
    ],
    [
        "Efisiensi OEE, ketersediaan, performa, mutu kualitas, dan produk yang diproduksi per lini produksi.",
        "ライン別のOEE総合効率、時間稼働率、性能稼働率、良品率、及び生産製品一覧"
    ],
    [
        "Realtime breakdown per tanggal produksi untuk masing-masing Shift",
        "生産日別・直別のリアルタイム実績内訳"
    ],
    [
        "Distribusi frekuensi defect aktual (Batang Rose) & Kurva Kumulatif 80/20 (Garis Neon Cyan)",
        "不良発生件数 (棒グラフ) 及び 80/20累積パレート曲線 (折れ線)"
    ],
    [
        "Distribusi produk cacat antara Assy, Rod, Cap & Fasteners",
        "Assy、Rod、Cap、締結部品別の不良分布"
    ],
    [
        "Area anatomi komponen manufaktur yang paling sering mengalami defect",
        "不良が最も多発している部品部位・箇所"
    ],
    [
        "Analisis prioritas perbaikan continuous improvement berbasis prinsip Kaizen & Six Sigma",
        "カイゼン及びシックスシグマに基づく重点改善項目の分析"
    ],
    [
        "Tidak ada catatan kendala mesin melebihi 5 menit pada rentang waktu ini. Lini beroperasi optimal.",
        "対象期間中に5分以上の設備停止記録はありません。正常稼働しています。"
    ],
    [
        "Daftar item NG per lini, mesin, jenis defect wajib OEE & pelengkap non-OEE beserta tindakan CAPA",
        "ライン別、設備別、OEE対象部品及び非OEE部品のNG明細一覧 (CAPA是正処置付き)"
    ],
    [
        "Daftar seluruh kendala teknis, waktu henti, analisa root cause, dan tindakan perbaikan (CAPA) tersinkron otomatis dari data harian produksi.",
        "生産日報から自動集計された技術トラブル、停止時間、根本原因分析、及び是正処置 (CAPA) 一覧"
    ],
    [
        "Master data terproteksi permanen dan tidak akan tersentuh saat pembersihan.",
        "マスタデータは完全に保護されており、データ初期化時も削除されません。"
    ],
    [
        "Tabel yang berisi riwayat aktivitas dan dapat dibersihkan untuk Go-Live.",
        "本番稼働前に初期化可能な実績履歴テーブルです。"
    ],
    [
        "Bersihkan data uji coba / simulasi agar aplikasi bersih dan siap digunakan secara resmi oleh operator & supervisor lini.",
        "テスト・検証用データを削除し、現場オペレーター及び管理者向けに正式運用を開始できる状態にします。"
    ],
    [
        "Hanya membersihkan tabel record produksi harian, snapshot kalkulasi OEE (A/P/Q), dan ringkasan shift.",
        "生産実績レコード、OEE計算スナップショット(A/P/Q)、直集計テーブルのみを初期化します。"
    ],
    [
        "Hanya membersihkan riwayat pencatatan breakdown mesin, kendala perbaikan, dan deskripsi CAPA.",
        "設備停止履歴、修繕記録、及びCAPA是正処置履歴のみを初期化します。"
    ],
    [
        "Hanya membersihkan antrean laporan NG, rincian item reject part, dan catatan sampling QC.",
        "NG報告キュー、不良部品明細、及びQCサンプリング記録のみを初期化します。"
    ],
    [
        "Bersihkan seluruh record hasil produksi, kalkulasi OEE, trouble log downtime, dan defect NG.",
        "全生産実績、OEE計算値、設備停止ログ、及び不良NGデータをすべて初期化します。"
    ],
    [
        "Master Data Shield: Aktif & Terproteksi",
        "マスタデータ保護シールド: 有効 (完全保護)"
    ],
    [
        "REKOMENDASI GO-LIVE",
        "本番稼働前 推奨設定"
    ],
    [
        "TRANSAKSI",
        "トランザクション"
    ],
    [
        "🔒 TERPROTEKSI",
        "🔒 保護対象"
    ],
    [
        "AMAN",
        "安全"
    ],
    [
        "BERSIHKAN",
        "初期化実行"
    ],
    [
        "Total Laporan Terkena NG:",
        "不良発生日報数:"
    ],
    [
        "Terverifikasi / Pending",
        "確認済 / 未確認"
    ],
    [
        "Dominant Component",
        "最多不良部品"
    ],
    [
        "Quality Yield & PPM",
        "良品歩留まり率及びPPM"
    ],
    [
        "TANGGAL AUDIT:",
        "監査日:"
    ],
    [
        "Total Kejadian Trouble",
        "総トラブル発生件数"
    ],
    [
        "Total Durasi Waktu Henti",
        "総設備停止時間"
    ],
    [
        "Rata-Rata Penanganan (MTTR)",
        "平均復旧時間 (MTTR)"
    ],
    [
        "Mesin Paling Terkendala",
        "ワースト停止設備"
    ],
    [
        "Line OEE Score:",
        "ラインOEEスコア:"
    ],
    [
        "ATTENTION: STOPPED / BREAKDOWN MACHINES",
        "警告: 停止中・故障中の設備"
    ],
    [
        "NEEDS IMMEDIATE ACTION",
        "即時対応が必要"
    ],
    [
        "REAL-TIME PREVIEW",
        "リアルタイム プレビュー"
    ],
    [
        "Logo Aktif Terpasang",
        "登録済みロゴ"
    ],
    [
        "Logo Siap Disimpan",
        "保存可能なロゴ"
    ],
    [
        "Database Engine",
        "データベースエンジン"
    ],
    [
        "Storage & Capacity",
        "ストレージ及び使用容量"
    ],
    [
        "Server Runtime",
        "サーバー実行環境"
    ],
    [
        "Search machine, line, SKU...",
        "設備、ライン、品番(SKU)で検索..."
    ],
    [
        "Cari kode/nama mesin...",
        "設備コード/設備名で検索..."
    ],
    [
        "Search records...",
        "レコード検索..."
    ],
    [
        "Penyebab Downtime",
        "停止要因"
    ],
    [
        "Durasi",
        "停止時間"
    ],
    [
        "Kontribusi",
        "寄与率・比率"
    ],
    [
        "Jenis Defect",
        "不良現象・種類"
    ],
    [
        "Bagian",
        "部位・箇所"
    ],
    [
        "Volume",
        "数量"
    ],
    [
        "Rekomendasi Kaizen",
        "カイゼン・是正提案"
    ],
    [
        "Availability",
        "時間稼働率"
    ],
    [
        "Performance",
        "性能稼働率"
    ],
    [
        "Quality",
        "良品率"
    ],
    [
        "Output Aktual",
        "実績出来高"
    ],
    [
        "Code",
        "コード"
    ],
    [
        "Machine Name",
        "設備名"
    ],
    [
        "Line",
        "ライン"
    ],
    [
        "Actual",
        "実績"
    ],
    [
        "Rejects",
        "不良品"
    ],
    [
        "Downtime",
        "停止時間"
    ],
    [
        "Product Name",
        "製品名"
    ],
    [
        "Shift",
        "直 (シフト)"
    ],
    [
        "Measuring",
        "実測数"
    ],
    [
        "NG Qty",
        "不良数"
    ],
    [
        "Reject Rate",
        "不良率"
    ],
    [
        "OEE Score",
        "OEEスコア"
    ],
    [
        "Waktu Trouble",
        "発生時刻"
    ],
    [
        "Line & Mesin",
        "ライン・設備"
    ],
    [
        "Shift & Team",
        "直・班"
    ],
    [
        "Product",
        "製品"
    ],
    [
        "Jenis Problem",
        "問題分類"
    ],
    [
        "Masalah / Kendala (Trouble)",
        "トラブル内容・症状"
    ],
    [
        "Line ID",
        "ラインID"
    ],
    [
        "Line Name",
        "ライン名"
    ],
    [
        "Machine",
        "設備"
    ],
    [
        "Product SKU",
        "製品型番 (SKU)"
    ],
    [
        "Avail",
        "稼働"
    ],
    [
        "Perf",
        "性能"
    ],
    [
        "Defect",
        "不良"
    ],
    [
        "Qual",
        "品質"
    ],
    [
        "Jam",
        "時間"
    ],
    [
        "Laporan",
        "件"
    ],

    // --- SIDEBAR AUTO HIDE & PINNING ---
    ["Kunci Sidebar (Fixed) - Klik untuk Mode Auto Hide", "サイドバー固定 (クリックで自動非表示)"],
    ["Buka Kunci Sidebar - Klik untuk Kunci Sidebar Tetap", "サイドバー自動非表示解除 (クリックで固定)"],
    ["Sidebar Dikunci (Pinned)", "サイドバーを固定しました"],
    ["Sidebar Auto Hide", "サイドバー自動非表示を有効化"],
    ["Sidebar akan tetap terbuka secara permanen.", "サイドバーが常時表示されます。"],
    ["Sidebar akan otomatis mengecil saat mouse diarahkan ke dashboard.", "マウスが画面に移動するとサイドバーが自動的に縮小します。"],

    // --- TV / DASHBOARD DISPLAY HEADERS & LABELS ---
    ["Macro KPIs Overview", "マクロKPIサマリー"],
    ["Grafik Performa OEE - Semua Line", "全ラインOEE稼働実績グラフ"],
    ["Resume Hasil Produksi Global", "グローバル生産実績サマリー"],
    ["OEE Semua Line (FX-1 s/d FX-11)", "全ラインOEE (FX-1〜FX-11)"],
    ["Output Per Line (Pcs)", "ライン別生産数 (個)"],
    ["Multi-Axis (OEE & Output)", "複合軸 (OEE & 出来高)"],
    ["Tren Tanggal", "日次推移トレンド"],
    ["Target Plan", "計画目標数"],
    ["Total Output", "総出来高"],
    ["Good Output (OK)", "良品数 (OK)"],
    ["Defect (NG)", "不良数 (NG)"],
    ["Total Downtime", "総停止時間"],
    ["Pencapaian Target:", "目標達成率:"],
    ["Pencapaian Target", "目標達成率"],
    ["Total Lini Terpantau:", "監視対象ライン数:"],
    ["Target Benchmark:", "目標基準値:"],
    ["Defect Rate:", "不良率:"],
    ["Siap kirim / Lolos QC", "出荷準備完了 / QC合格"],
    ["Perlu countermeasure", "要是正処置 (Countermeasure)"],
    ["Zero Defect", "ゼロディフェクト (不良ゼロ)"],
    ["Waktu henti produksi", "製造停止時間"],
    ["baseline target", "基準目標"],
    ["pcs gap", "個 差異"],
    ["Total Downtime Loss", "総停止時間ロス"],
    ["World Class Std: 85%", "ワールドクラス基準: 85%"],
    ["Reject / Scrap Qty", "不良・廃棄数量"],
    ["Exit TV Mode", "TVモード終了"],
    ["TV FLOOR DISPLAY", "工場フロア大型表示"],
    ["LIVE REALTIME", "リアルタイム更新中"],
    ["⭐ All Measuring Machines", "⭐ 全計測号機 (Measuring Machines)"],
    ["⭐ Semua Mesin Pengukuran", "⭐ 全計測号機 (Measuring Machines)"],
    ["Semua Mesin Pengukuran", "全計測号機"],
    ["Semua Mesin", "全号機 (All Machines)"],
    ["Semua Lini Produksi", "全生産ライン"],
    ["Semua Lini", "全ライン"],
    ["Semua Shift", "全直 (All Shifts)"],

    // --- NG QUEUE & COMPONENT SECTION LABELS ---
    ["Daftar Antrean Laporan Harian yang Memiliki Reject / Defect", "不良・NG発生 日次報告一覧"],
    ["Antrean Laporan NG", "NG登録待ち報告"],
    ["STATUS INPUT DETAIL", "明細入力ステータス"],
    ["KOMPONEN WAJIB (OEE)", "主要部品 (OEE対象)"],
    ["PELENGKAP (NON-OEE)", "締結部品 (非OEE)"],
    ["Komponen Utama (Wajib OEE)", "主要部品 (OEE対象)"],
    ["Komponen Pelengkap (Non-OEE)", "締結部品 (非OEE)"],
    ["Komponen Utama NG", "主要NG構成品 (OEE対象)"],
    ["Collapse All", "すべて折りたたむ"],
    ["Expand All", "すべて展開"],
    ["Klik baris", "行をクリック"],
    ["Sudah Lengkap:", "登録完了:"],
    ["Belum Lengkap:", "未完了:"],
    ["Total Target NG:", "総目標不良数:"],
    ["Entry NG Baru", "新規NG登録"],
    ["Entry Detail NG", "NG明細入力"],
    ["Tambah Baris NG", "NG明細行を追加"],
    ["Hapus Baris", "行を削除"],

    // --- SYSTEM & NAVIGATION MODULES ---
    ["OPERASIONAL & INPUT DATA", "現場運用・実績入力"],
    ["Input Laporan Harian", "日次生産実績入力"],
    ["Input Laporan NG", "不良実績・NG入力"],
    ["Monitoring Produksi", "ライン進捗モニター"],
    ["ANALISIS & SPC", "分析・統計的工程管理 (SPC)"],
    ["Kinerja Mesin", "号機別稼働実績"],
    ["Lini Produksi", "ライン別実績"],
    ["Kinerja Shift & Tim", "直・班別実績 (Shift & Team)"],
    ["Kinerja Shift", "直別実績 (シフト比較)"],
    ["Kinerja Tim", "班別実績 (チーム比較)"],
    ["Analisis Downtime", "停止時間分析 (パレート)"],
    ["Kinerja Kualitas", "品質・良品率実績"],
    ["MASTER DATA & LAPORAN", "マスタ管理及び各種日報"],
    ["Laporan & Ekspor Data", "帳票・実績日報出力"],
    ["Master Data", "マスタ管理"],
    ["SISTEM & AKSES", "システム管理・権限"],
    ["Manajemen Pengguna", "ユーザー権限管理"],
    ["Pengaturan Sistem", "システム環境設定"],
    ["Manajemen Basis Data", "データベース管理"],
    ["TV / Floor Display Mode", "アンドン・工場大型表示 (TV Mode)"],
    ["Keluar / Logout", "ログアウト"],
    ["Keluar", "ログアウト"],
    ["Dasbor", "ダッシュボード"]
];

// Pre-sort longest phrases first for substring replacement integrity
MONOZUKURI_MASTER_DICTIONARY.sort((a, b) => b[0].length - a[0].length);

class I18nService {
    constructor() {
        this.currentLocale = this.loadLocale();
        this.listeners = [];
    }

    /**
     * Load locale from localStorage, default to 'id'
     */
    loadLocale() {
        try {
            if (typeof localStorage !== 'undefined') {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (saved === 'ja' || saved === 'id') {
                    return saved;
                }
            }
        } catch (e) {
            console.warn('Unable to access localStorage for i18n locale:', e);
        }
        return 'id';
    }

    /**
     * Get active locale code ('id' | 'ja')
     */
    getLocale() {
        return this.currentLocale;
    }

    /**
     * Set active locale, save to localStorage, and notify listeners
     */
    setLocale(locale) {
        if (locale !== 'id' && locale !== 'ja') return false;
        this.currentLocale = locale;
        try {
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem(STORAGE_KEY, locale);
            }
        } catch (e) {
            console.warn('Unable to persist i18n locale:', e);
        }

        // Set lang attribute on html tag for font fallback & accessibility
        if (typeof document !== 'undefined' && document.documentElement) {
            document.documentElement.setAttribute('lang', locale);
        }

        // Notify subscribers
        this.listeners.forEach(fn => {
            try { fn(locale); } catch (err) { console.error('i18n listener error:', err); }
        });

        // Dispatch window CustomEvent for external listeners
        if (typeof window !== 'undefined' && window.dispatchEvent) {
            window.dispatchEvent(new CustomEvent('app:locale-changed', { detail: { locale } }));
        }
        return true;
    }

    /**
     * Subscribe to locale changes
     */
    subscribe(listener) {
        if (typeof listener === 'function') {
            this.listeners.push(listener);
        }
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    }

    /**
     * Translate key using dot notation (e.g. 'dashboard.oee_score')
     */
    t(keyPath, fallback = '') {
        if (!keyPath) return fallback;
        const parts = String(keyPath).split('.');
        const domain = parts[0];
        const key = parts[1];

        if (translations[domain] && translations[domain][this.currentLocale]) {
            const val = translations[domain][this.currentLocale][key];
            if (val !== undefined && val !== null) {
                return val;
            }
        }

        // Fallback to Indonesian if Japanese key is missing
        if (this.currentLocale !== 'id' && translations[domain]?.id?.[key] !== undefined) {
            return translations[domain].id[key];
        }

        return fallback || key || keyPath;
    }

    /**
     * Get metadata of supported languages
     */
    getSupportedLanguages() {
        return [
            { code: 'id', label: 'Bahasa Indonesia', short: 'ID', flag: '🇮🇩' },
            { code: 'ja', label: '日本語 (Japanese)', short: 'JP', flag: '🇯🇵' },
        ];
    }

    /**
     * Dynamically localize all DOM elements within a container when currentLocale is 'ja'
     */
    localizeDom(container) {
        if (!container || this.currentLocale !== 'ja') return;

        // 1. Comprehensive Table Headers Mapping (lowercase key → Japanese value)
        const thMap = {
            // General columns
            'no': 'No',
            'action': '操作',
            'actions': '操作',
            'aksi': '操作',
            'status': '状態',
            'status aktif': '有効状態',
            'status input': '入力状態',
            'status verifikasi': '検証状態',
            'status evaluasi': '評価状態',
            'status classification': '稼働状態区分',
            'status / pic': '状態 / 担当',
            'kategori': 'カテゴリ',
            'kategori master': 'マスタ分類',
            'kategori masalah': '問題カテゴリ',
            'klasifikasi': '分類',
            'keterangan': '備考',
            'type': '種別',
            'code': 'コード',
            'rank': '順位',
            'bagian': '部品部位',
            'ukuran': 'サイズ',
            'volume': '数量',
            'category': 'カテゴリ',

            // Line / Machine columns
            'line': 'ライン',
            'line id': 'ライン ID',
            'line name': 'ライン名',
            'line id & name': 'ラインID・名称',
            'line & mesin': 'ライン・設備',
            'lini & mesin': 'ライン・設備',
            'line produksi': '生産ライン',
            'lini produksi': '生産ライン',
            'production line': '生産ライン',
            'line / location': 'ライン / 場所',
            'location': '場所',
            'plant / location': '工場 / 拠点',
            'plant name': '工場名',
            'machine': '設備',
            'machine name': '設備名称',
            'mesin': '設備',
            'kode mesin': '設備コード',
            'kode & nama mesin': '設備コード・名称',
            'mesin & line': '設備・ライン',
            'mesin terkendala': '異常設備',
            'machine & produk - sku': '設備・製品型番 (SKU)',
            'idmc / code': 'IDMC / コード',

            // Product columns
            'product': '製品',
            'product name': '品名・品番',
            'product name (nama produk)': '品名 (製品名称)',
            'product sku': '製品 SKU',
            'produk - sku': '製品 - SKU',
            'part / sku': '部品 / SKU',
            'sku': 'SKU',
            'type produk (sku)': '品名・型番 (SKU)',

            // Shift & Time columns
            'shift': '直 (シフト)',
            'shift name': '直名称',
            'shift & team': '直・班',
            'team': '班',
            'tanggal': '日付',
            'tanggal & shift': '生産日・直',
            'tanggal & jam': '日付・時刻',
            'tanggal & waktu': '日付・時刻',
            'tanggal produksi': '製造日',
            'jam': '時刻',
            'jam kejadian': '発生時刻',
            'jam trouble': 'トラブル発生時刻',
            'jam kerja (working hours)': '稼働時間 (Working Hours)',
            'jam istirahat (break)': '休憩時間 (Break)',
            'waktu trouble': '発生日時',
            'waktu henti': '停止時間帯',
            'waktu total (menit)': '合計時間 (分)',

            // Production output columns
            'target plan': '計画目標数',
            'target': '目標計画数',
            'target speed': '基準速度',
            'target standar': '標準目標',
            'target oee': '目標 OEE',
            'target ng': '目標不良数',
            'standard speed': '基準速度',
            'speed (p/m)': '速度 (個/分)',
            'ideal cycle time': '理想サイクルタイム',
            'measuring': '実測数',
            'total measuring': '総実測数',
            'actual': '実績',
            'output': '総生産数',
            'output (pcs)': '総出来高 (個)',
            'output aktual': '実績出来高',
            'total output': '総出来高',
            'total output produksi': '総生産出来高',
            'finish good': '良品数',
            'good output (ok)': '良品数 (OK)',
            'ok': '良品',
            'ok (pcs)': '良品数 (OK)',
            'pencapaian aktual': '実績達成',

            // OEE metrics columns
            'avail': '稼働率',
            'avail (%)': '稼働率 (%)',
            'availability': '稼働率',
            'perf': '性能率',
            'perf (%)': '性能率 (%)',
            'performance': '性能率',
            'qual': '良品率',
            'qual (%)': '良品率 (%)',
            'quality': '良品率',
            'quality (%)': '良品率 (%)',
            'quality yield (%)': '良品率 (%)',
            'quality yield rate (%)': '良品率 (%)',
            'oee': 'OEE',
            'oee (%)': 'OEE (%)',
            'oee %': 'OEE %',
            'oee score': 'OEE 効率',
            'overall oee': '総合 OEE',
            'rasio / standar': '比率 / 基準',
            'parameter metrik': '計測パラメータ',
            'parameter mutu & output': '品質・出来高パラメータ',
            'pilar oee terkait': '関連 OEE 柱',
            'komponen oee *': 'OEE 構成要素 *',

            // Planned time columns
            'plan (m)': '計画 (分)',
            'planned (min)': '計画負荷 (分)',
            'planned production time (waktu efektif)': '計画稼働時間 (有効時間)',
            'planned maintenance': '計画保全',

            // Downtime / Trouble columns
            'down (min)': '停止 (分)',
            'down (m)': '停止 (分)',
            'downtime': '停止時間',
            'durasi': '停止時間',
            'durasi (menit)': '停止時間 (分)',
            'durasi (jam)': '停止時間 (時)',
            'durasi (m)': '停止時間 (分)',
            'total downtime (menit)': '総停止時間 (分)',
            'total downtime (jam)': '総停止時間 (時)',
            'total waktu henti': '総停止時間',
            'total kejadian': '総発生件数',
            'total kejadian (calls)': '総発生件数 (回)',
            'frekuensi': '発生頻度',
            'jenis problem': 'トラブル分類',
            'masalah / kendala (trouble)': '異常・トラブル内容',
            'penyebab downtime': '停止原因',
            'gejala & root cause trouble': '症状・根本原因',
            'gejala & root cause masalah': '症状・根本原因',
            'akar masalah (5 whys rca)': '根本原因 (5 Whys RCA)',
            'tindakan perbaikan (capa)': '是正処置 (CAPA)',
            'tindakan perbaikan (capa) & pic': '是正処置 (CAPA) & 担当者',
            'tindakan capa': '是正処置 (CAPA)',
            'rekomendasi kaizen': '改善提案',
            'rekomendasi kaizen / countermeasure': '改善提案 / 対策',
            'rekomendasi tindakan korektif (countermeasure)': '是正処置提案 (Countermeasure)',
            'rata-rata mttr (menit)': '平均 MTTR (分)',
            'rata-rata penanganan (mttr)': '平均対応時間 (MTTR)',
            'id incident': 'インシデント ID',
            'unplanned breakdown': '計画外故障',
            'unplanned vs planned': '計画外 vs 計画',
            'kategori six big loss': '六大ロス分類',
            'kategori alokasi waktu': '時間配分分類',
            'kontribusi': '寄与度',
            '% kontribusi loss': '% ロス寄与度',
            'kumulatif (%)': '累積 (%)',
            'deviasi': '偏差',

            // Defect / Quality columns
            'defect': '不良',
            'defect (pcs)': '不良数 (個)',
            'defect (rej+scrap)': '不良数 (NG+損)',
            'defect %': '不良率',
            'defect rate (%)': '不良率 (%)',
            'total ng': '総不良数',
            'ng qty': '不良数',
            'ng (pcs)': '不良数 (個)',
            'not good (ng)': '不良品 (NG)',
            'reject qty': '不良数量',
            'reject rate': '不良率',
            'rejects': '不良品',
            'total reject / ng': '総不良品 / NG',
            'jenis defect': '不良種別',
            'kategori defect': '不良カテゴリ',
            'kategori & masalah defect': '不良現象・要因分類',
            'masalah / kategori defect': '不良原因 / カテゴリ',
            'gejala defect': '不良症状',
            'penyebab defect / remark': '不良原因 / 備考',
            'penyebab / remark (ketik / cari)': '原因 / 備考 (入力 / 検索)',
            'nama penyebab / remark': '原因名 / 備考',
            'inspector': '検査員',
            'deskripsi fungsi': '機能説明',

            // NG component columns
            'rincian komponen wajib (oee)': '主要部品明細 (OEE)',
            'wajib oee (assy/rod/cap)': '主要部品 (Assy/Rod/Cap)',
            'wajib oee (a/r/c)': '主要部品 (A/R/C)',
            'pelengkap (bolt/bush/nut/pin)': '締結部品 (Bolt/Bush/Nut/Pin)',
            'pelengkap (non-oee)': '補助部品 (Non-OEE)',
            'pelengkap (b/bs/n/p)': '補助部品 (B/Bs/N/P)',
            'non-oee (b/bs/n/p)': 'Non-OEE (B/Bs/N/P)',
            'bagian ng (section)': 'NG 部品部位',
            'bagian ng (ketik / cari)': 'NG 部品 (入力 / 検索)',
            'kode bagian ng': 'NG 部品コード',
            'nama bagian ng': 'NG 部品名',
            'jumlah (pcs)': '数量 (個)',
            'jumlah (pcs) *': '数量 (個) *',
            'jumlah baris': '行数',

            // Reason columns
            'reason name': '原因名称',

            // User management columns
            'user profile & name': 'ユーザー情報・氏名',
            'corporate email (@prodcr.yasunaga.com)': '社用メール (@prodcr.yasunaga.com)',
            'assigned role(s)': '権限・ロール',
            'account status': 'アカウント状態',
            'supervisor name': '監督者名',
            'leader name / pic': 'リーダー名 / 担当',
            'pic / tim': '担当 / チーム',

            // Database management columns
            'nama tabel': 'テーブル名',
            'data tersimpan': '保存データ数',

            // Rate columns
            'rate (%)': '率 (%)',
        };

        // 2. Select Option Texts (regex-based for dynamic counts)
        container.querySelectorAll('option').forEach(opt => {
            let raw = opt.textContent;
            if (/semua status/i.test(raw)) raw = raw.replace(/semua status/i, '全ステータス');
            if (/belum diisi/i.test(raw)) raw = raw.replace(/belum diisi/i, '未入力');
            if (/selesai terisi/i.test(raw)) raw = raw.replace(/selesai terisi/i, '入力完了');
            if (/semua shift/i.test(raw)) raw = raw.replace(/semua shift/i, '全直 (All Shifts)');
            if (/semua lini produksi/i.test(raw)) raw = raw.replace(/semua lini produksi/i, '全生産ライン');
            if (/semua line/i.test(raw)) raw = raw.replace(/semua line/i, '全ライン (All Lines)');
            if (/semua mesin/i.test(raw)) raw = raw.replace(/semua mesin/i, '全号機 (All Machines)');
            if (/semua type produk/i.test(raw)) raw = raw.replace(/semua type produk/i, '全製品・型番 (All Products)');
            if (/perlu input/i.test(raw)) raw = raw.replace(/perlu input/i, '入力必要');
            opt.textContent = raw;
        });

        // 3. TH elements — walk TEXT NODES inside each TH to avoid clobbering sort indicator spans
        container.querySelectorAll('th').forEach(th => {
            for (let child of th.childNodes) {
                if (child.nodeType === 3 /* TEXT_NODE */) {
                    const trimmed = child.nodeValue.trim().toLowerCase();
                    if (trimmed && thMap[trimmed]) {
                        child.nodeValue = child.nodeValue.replace(child.nodeValue.trim(), thMap[trimmed]);
                    }
                }
            }
        });

        // 4. Comprehensive Phrase Dictionary
        const sortedPhrases = MONOZUKURI_MASTER_DICTIONARY;

        // 5. Status Badges & Pills
        container.querySelectorAll('.btn-quick-status-filter, .status-badge, [class*="status-"], .badge').forEach(el => {
            const raw = el.textContent;
            if (raw.includes('RUNNING')) el.textContent = el.textContent.replace('RUNNING', '稼働中');
            else if (raw.includes('Running')) el.textContent = el.textContent.replace('Running', '稼働中');
            else if (raw.includes('IDLE')) el.textContent = el.textContent.replace('IDLE', '待機中');
            else if (raw.includes('Idle')) el.textContent = el.textContent.replace('Idle', '待機中');
            else if (raw.includes('STOP')) el.textContent = el.textContent.replace('STOP', '停止中');
            else if (raw.includes('Stop')) el.textContent = el.textContent.replace('Stop', '停止中');
            else if (raw.includes('BREAKDOWN')) el.textContent = el.textContent.replace('BREAKDOWN', '故障停止');
        });

        // 6. Deep Recursive Tree Walker: translates ALL text nodes and attributes across all child elements
        const walk = (node) => {
            if (node.nodeType === 3 /* Node.TEXT_NODE */) {
                let text = node.nodeValue;
                if (text && text.trim()) {
                    for (let i = 0; i < sortedPhrases.length; i++) {
                        const [from, to] = sortedPhrases[i];
                        if (text.includes(from)) {
                            text = text.split(from).join(to);
                        }
                    }
                    node.nodeValue = text;
                }
            } else if (node.nodeType === 1 /* Node.ELEMENT_NODE */) {
                if (node.tagName === 'SCRIPT' || node.tagName === 'STYLE') return;

                if (node.hasAttribute('placeholder')) {
                    let ph = node.getAttribute('placeholder');
                    for (let i = 0; i < sortedPhrases.length; i++) {
                        if (ph.includes(sortedPhrases[i][0])) {
                            ph = ph.split(sortedPhrases[i][0]).join(sortedPhrases[i][1]);
                        }
                    }
                    node.setAttribute('placeholder', ph);
                }

                if (node.hasAttribute('title')) {
                    let title = node.getAttribute('title');
                    for (let i = 0; i < sortedPhrases.length; i++) {
                        if (title.includes(sortedPhrases[i][0])) {
                            title = title.split(sortedPhrases[i][0]).join(sortedPhrases[i][1]);
                        }
                    }
                    node.setAttribute('title', title);
                }

                for (let child of node.childNodes) {
                    walk(child);
                }
            }
        };

        walk(container);
    }

    /**
     * Translate arbitrary text string into Japanese when locale is 'ja'
     */
    translateText(text) {
        if (!text || typeof text !== 'string' || this.currentLocale !== 'ja') return text;

        let out = text;
        for (let i = 0; i < MONOZUKURI_MASTER_DICTIONARY.length; i++) {
            if (out.includes(MONOZUKURI_MASTER_DICTIONARY[i][0])) {
                out = out.split(MONOZUKURI_MASTER_DICTIONARY[i][0]).join(MONOZUKURI_MASTER_DICTIONARY[i][1]);
            }
        }
        return out;
    }

    /**
     * Translate an entire HTML report or export template string into professional Japanese when currentLocale === 'ja'
     */
    translateHtml(html) {
        if (!html || typeof html !== 'string' || this.currentLocale !== 'ja') return html;

        let out = html;
        for (let i = 0; i < MONOZUKURI_MASTER_DICTIONARY.length; i++) {
            const [from, to] = MONOZUKURI_MASTER_DICTIONARY[i];
            if (out.includes(from)) {
                out = out.split(from).join(to);
            }
        }
        return out;
    }
}

export const i18n = new I18nService();
export default i18n;
