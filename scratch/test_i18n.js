import { i18n } from '../resources/js/services/i18n.js';

i18n.setLocale('ja');
console.log('--- TEST TRANSLATETEXT ---');
const tests = [
    'Detail Laporan Produksi #99',
    'Tambah Mesin',
    'Tindakan ini tidak dapat dibatalkan!',
    'Corporate Email / Username',
    'Status Akun Aktif (Active)',
    'Hapus Log Trouble #4',
    'Simpan Perubahan & Hitung Ulang OEE',
    'Konfirmasi Pembersihan Data Go-Live',
    'Edit Rekomendasi Tindakan Korektif',
    'Log Entry Record: MC-01 (Milling)',
];

for (const t of tests) {
    console.log(t.padEnd(38), '->', i18n.translateText(t));
}
