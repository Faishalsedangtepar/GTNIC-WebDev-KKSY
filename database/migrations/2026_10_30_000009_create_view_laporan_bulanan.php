<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Laporan bulanan (admin). Bulan dihitung dalam WIB, bukan UTC server Supabase,
        // supaya nota jam 00:30 tgl 1 tidak masuk bulan sebelumnya.
        // Pakai: SELECT * FROM v_laporan_bulanan WHERE bulan = '2026-10-01'
        DB::statement("
            CREATE VIEW v_laporan_bulanan AS
            SELECT date_trunc('month', tanggal AT TIME ZONE 'Asia/Jakarta')::date AS bulan,
                   COUNT(*)                AS jumlah_nota,
                   SUM(total)              AS total_penjualan,
                   SUM(dibayar)            AS total_dibayar,
                   SUM(total - dibayar)    AS sisa_tagihan
            FROM nota
            GROUP BY 1
            ORDER BY 1 DESC
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_laporan_bulanan');
    }
};
