<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota', function (Blueprint $t) {
            $t->id();
            $t->timestampTz('tanggal')->useCurrent();
            $t->foreignId('kasir_id')->constrained('pengguna');
            $t->decimal('total', 14, 2)->default(0);            // diisi aplikasi
            $t->decimal('dibayar', 14, 2)->default(0);

            // dihitung database: belum_dibayar | dp | lunas
            $t->string('status_bayar', 15)->storedAs(
                "CASE WHEN total > 0 AND dibayar >= total THEN 'lunas' " .
                "WHEN dibayar > 0 THEN 'dp' ELSE 'belum_dibayar' END"
            );
        });

        DB::statement('ALTER TABLE nota ADD CONSTRAINT nota_dibayar_nonneg CHECK (dibayar >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('nota');
    }
};
