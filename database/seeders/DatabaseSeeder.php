<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            ['admin', 'admin', env('SEED_ADMIN_PASSWORD')],
            ['kasir', 'kasir', env('SEED_KASIR_PASSWORD')],
        ];

        foreach ($akun as [$username, $role, $password]) {
            if (! $password) {
                throw new \RuntimeException("SEED_" . strtoupper($role) . "_PASSWORD belum diisi di .env");
            }

            Pengguna::updateOrCreate(
                ['username' => $username],
                ['password_hash' => Hash::make($password), 'role' => $role],
            );
        }
    }
}