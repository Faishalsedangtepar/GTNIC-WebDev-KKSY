<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keranjang_item', function (Blueprint $t) {
            $t->id();
            $t->foreignId('produk_id')->unique()->constrained('produk');   // produk sama = update qty
            $t->decimal('qty', 12, 2);
        });

        DB::statement('ALTER TABLE keranjang_item ADD CONSTRAINT keranjang_item_qty_pos CHECK (qty > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjang_item');
    }
};
