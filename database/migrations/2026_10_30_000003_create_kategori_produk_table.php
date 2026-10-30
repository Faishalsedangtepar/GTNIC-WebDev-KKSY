<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_produk', function (Blueprint $t) {
            $t->smallIncrements('id');
            $t->string('nama', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_produk');
    }
};
