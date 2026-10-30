<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_item', function (Blueprint $t) {
            $t->id();
            $t->foreignId('nota_id')->constrained('nota')->cascadeOnDelete();
            $t->foreignId('produk_id')->constrained('produk');
            $t->decimal('harga_satuan', 14, 2);                 // harga saat transaksi
            $t->decimal('qty', 12, 2);
            $t->decimal('subtotal', 14, 2)->storedAs('harga_satuan * qty');
        });

        DB::statement('ALTER TABLE nota_item ADD CONSTRAINT nota_item_harga_nonneg CHECK (harga_satuan >= 0)');
        DB::statement('ALTER TABLE nota_item ADD CONSTRAINT nota_item_qty_pos CHECK (qty > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_item');
    }
};
