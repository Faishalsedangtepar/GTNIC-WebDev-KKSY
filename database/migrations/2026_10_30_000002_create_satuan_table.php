<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satuan', function (Blueprint $t) {
            $t->smallIncrements('id');
            $t->string('nama', 30)->unique();
        });

        DB::table('satuan')->insert([['nama' => 'Sak'], ['nama' => 'Kubik']]);
    }

    public function down(): void
    {
        Schema::dropIfExists('satuan');
    }
};
