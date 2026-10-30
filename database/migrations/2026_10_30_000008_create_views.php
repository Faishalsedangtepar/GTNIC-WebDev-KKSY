<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Layar Produk. Search: WHERE nama ILIKE '%x%'. Filter: WHERE kategori = 'Semen'
        DB::statement("
            CREATE VIEW v_produk AS
            SELECT p.id, p.nama, k.nama AS kategori, s.nama AS satuan,
                   p.deskripsi, p.harga_jual, p.stok
            FROM produk p
            JOIN kategori_produk k ON k.id = p.kategori_id
            JOIN satuan s          ON s.id = p.satuan_id
        ");

        // Total Harga = Harga x Kuantitas
        DB::statement("
            CREATE VIEW v_keranjang AS
            SELECT ki.id AS item_id, p.id AS produk_id, p.nama,
                   s.nama AS satuan, p.harga_jual, ki.qty,
                   (p.harga_jual * ki.qty) AS total_harga
            FROM keranjang_item ki
            JOIN produk p ON p.id = ki.produk_id
            JOIN satuan s ON s.id = p.satuan_id
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_keranjang');
        DB::statement('DROP VIEW IF EXISTS v_produk');
    }
};
