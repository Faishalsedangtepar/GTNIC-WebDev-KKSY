<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $t) {
            $t->id();
            $t->string('username', 50)->unique();
            $t->text('password_hash');
            $t->enum('role', ['kasir', 'admin']);   // AuthorizeType
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
