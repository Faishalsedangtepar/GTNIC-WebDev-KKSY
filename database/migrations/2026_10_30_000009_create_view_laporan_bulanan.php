<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
        CREATE VIEW v_laporan_bulanan AS
        SELECT DATE_FORMAT(tanggal, '%Y-%m-01') AS bulan,
            COUNT(*)             AS jumlah_nota,
            SUM(total)           AS total_penjualan,
            SUM(dibayar)         AS total_dibayar,
            SUM(total - dibayar) AS sisa_tagihan
        FROM nota
        GROUP BY DATE_FORMAT(tanggal, '%Y-%m-01')
        ORDER BY bulan DESC
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_laporan_bulanan');
    }
};
