<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 150);
            $t->unsignedSmallInteger('kategori_id');
            $t->unsignedSmallInteger('satuan_id');
            $t->text('deskripsi')->nullable();                  // Detail Produk
            $t->decimal('harga_jual', 14, 2);
            $t->decimal('stok', 12, 2)->default(0);             // desimal: kubik bisa 2,5

            $t->foreign('kategori_id')->references('id')->on('kategori_produk');
            $t->foreign('satuan_id')->references('id')->on('satuan');
        });

        DB::statement('ALTER TABLE produk ADD CONSTRAINT produk_harga_nonneg CHECK (harga_jual >= 0)');
        DB::statement('ALTER TABLE produk ADD CONSTRAINT produk_stok_nonneg CHECK (stok >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
