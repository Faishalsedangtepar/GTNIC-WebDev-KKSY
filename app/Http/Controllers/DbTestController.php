<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * Halaman tes koneksi & skema database (Supabase/PostgreSQL).
 * Hanya aktif saat APP_ENV=local. Hapus file ini sebelum deploy.
 */
class DbTestController extends Controller
{
    private const TABLES = ['pengguna', 'satuan', 'kategori_produk', 'produk', 'nota', 'nota_item', 'keranjang_item'];
    private const VIEWS  = ['v_produk', 'v_keranjang'];

    private function guard(): void
    {
        abort_unless(app()->environment('local'), 404);
    }

    public function index(): View
    {
        $this->guard();

        return view('dbtest', ['status' => $this->status()]);
    }

    /** Info koneksi + cek keberadaan tabel/view + jumlah baris. */
    private function status(): array
    {
        $conn = config('database.default');
        $cfg  = config("database.connections.$conn");

        $out = [
            'connected' => false,
            'driver'    => $cfg['driver'] ?? '-',
            'host'      => $cfg['host'] ?? '-',
            'port'      => $cfg['port'] ?? '-',
            'database'  => $cfg['database'] ?? '-',
            'username'  => $cfg['username'] ?? '-',
            'sslmode'   => $cfg['sslmode'] ?? '-',
            'objects'   => [],
        ];

        try {
            $t = microtime(true);
            DB::connection()->getPdo();                       // paksa konek
            $out['connect_ms'] = round((microtime(true) - $t) * 1000, 1);

            $t   = microtime(true);
            $row = DB::selectOne('select version() as v, current_database() as db, current_user as usr, now() as now');
            $out['ping_ms'] = round((microtime(true) - $t) * 1000, 1);

            $out['connected']  = true;
            $out['version']    = $row->v;
            $out['server_now'] = $row->now;

            $existing = collect(DB::select(
                'select table_name, table_type from information_schema.tables where table_schema = current_schema()'
            ))->pluck('table_type', 'table_name');

            foreach ([...self::TABLES, ...self::VIEWS] as $name) {
                $present = $existing->has($name);
                $count   = null;

                if ($present) {
                    try {
                        $count = DB::table($name)->count();
                    } catch (Throwable) {
                    }
                }

                $out['objects'][] = [
                    'name'    => $name,
                    'kind'    => in_array($name, self::VIEWS, true) ? 'view' : 'tabel',
                    'present' => $present,
                    'count'   => $count,
                ];
            }
        } catch (Throwable $e) {
            $out['error'] = strtok($e->getMessage(), "\n");
        }

        return $out;
    }

    /**
     * Smoke test CRUD + constraint. Semua dibungkus transaksi lalu di-ROLLBACK,
     * jadi tidak ada data yang tersisa (sequence ID tetap maju, itu normal di Postgres).
     */
    public function run(): JsonResponse
    {
        $this->guard();

        $steps = [];
        $ctx   = [];
        $start = microtime(true);

        // Tiap langkah jalan di savepoint, supaya satu kegagalan tidak membatalkan transaksi utama.
        $step = function (string $name, callable $fn, bool $expectFail = false) use (&$steps, &$ctx) {
            $t = microtime(true);
            try {
                $detail = DB::transaction(function () use ($fn, &$ctx) {
                    return $fn($ctx);
                });
                $ok     = ! $expectFail;
                $detail = $expectFail ? 'Seharusnya ditolak database, tapi lolos.' : (string) $detail;
            } catch (Throwable $e) {
                $ok     = $expectFail;
                $detail = ($expectFail ? 'Ditolak sesuai harapan: ' : '') . mb_substr(strtok($e->getMessage(), "\n"), 0, 200);
            }
            $steps[] = ['name' => $name, 'ok' => $ok, 'detail' => $detail, 'ms' => round((microtime(true) - $t) * 1000, 1)];
        };

        try {
            DB::beginTransaction();

            $step('Insert pengguna (kasir)', function (&$c) {
                $c['kasir'] = DB::table('pengguna')->insertGetId([
                    'username'      => 'tes_' . bin2hex(random_bytes(4)),
                    'password_hash' => Hash::make('rahasia'),
                    'role'          => 'kasir',
                ]);
                return "id = {$c['kasir']}";
            });

            $step('Baca satuan (seed Sak/Kubik)', function (&$c) {
                $row = DB::table('satuan')->where('nama', 'Sak')->first();
                if (! $row) {
                    throw new \RuntimeException("Satuan 'Sak' tidak ada. Migrasi satuan belum jalan / seed hilang.");
                }
                $c['satuan'] = $row->id;
                return "Sak id = {$row->id}";
            });

            $step('Insert kategori_produk', function (&$c) {
                $c['kategori'] = DB::table('kategori_produk')->insertGetId(['nama' => 'Semen__tes_' . bin2hex(random_bytes(3))]);
                return "id = {$c['kategori']}";
            });

            $step('Insert produk (harga 65.000, stok 10)', function (&$c) {
                $c['produk'] = DB::table('produk')->insertGetId([
                    'nama'        => 'Semen Tes',
                    'kategori_id' => $c['kategori'],
                    'satuan_id'   => $c['satuan'],
                    'deskripsi'   => 'data uji, akan di-rollback',
                    'harga_jual'  => 65000,
                    'stok'        => 10,
                ]);
                return "id = {$c['produk']}";
            });

            $step('View v_produk (join kategori & satuan)', function (&$c) {
                $r = DB::table('v_produk')->where('id', $c['produk'])->first();
                if (! $r || $r->satuan !== 'Sak') {
                    throw new \RuntimeException('Baris tidak muncul di v_produk atau join satuan salah.');
                }
                return "kategori = {$r->kategori}, satuan = {$r->satuan}";
            });

            $step('Keranjang qty 2,5 → v_keranjang.total_harga', function (&$c) {
                DB::table('keranjang_item')->insert(['produk_id' => $c['produk'], 'qty' => 2.5]);
                $r = DB::table('v_keranjang')->where('produk_id', $c['produk'])->first();
                if (! $r || abs((float) $r->total_harga - 162500) > 0.001) {
                    throw new \RuntimeException('total_harga = ' . ($r->total_harga ?? 'null') . ', harusnya 162500.');
                }
                return "total_harga = {$r->total_harga}";
            });

            $step('Keranjang: produk sama dua kali (UNIQUE)', function (&$c) {
                DB::table('keranjang_item')->insert(['produk_id' => $c['produk'], 'qty' => 1]);
            }, expectFail: true);

            $step('Insert nota → status_bayar generated', function (&$c) {
                $c['nota'] = DB::table('nota')->insertGetId(['kasir_id' => $c['kasir'], 'total' => 162500, 'dibayar' => 0]);
                $get       = fn () => DB::table('nota')->where('id', $c['nota'])->value('status_bayar');
                $hasil     = [$get()];

                DB::table('nota')->where('id', $c['nota'])->update(['dibayar' => 50000]);
                $hasil[] = $get();

                DB::table('nota')->where('id', $c['nota'])->update(['dibayar' => 162500]);
                $hasil[] = $get();

                if ($hasil !== ['belum_dibayar', 'dp', 'lunas']) {
                    throw new \RuntimeException('Urutan status salah: ' . implode(' → ', $hasil));
                }
                return implode(' → ', $hasil);
            });

            $step('Insert nota_item → subtotal generated', function (&$c) {
                $id = DB::table('nota_item')->insertGetId([
                    'nota_id' => $c['nota'], 'produk_id' => $c['produk'], 'harga_satuan' => 65000, 'qty' => 2.5,
                ]);
                $sub = DB::table('nota_item')->where('id', $id)->value('subtotal');
                if (abs((float) $sub - 162500) > 0.001) {
                    throw new \RuntimeException("subtotal = $sub, harusnya 162500.");
                }
                return "subtotal = $sub";
            });

            $step('CHECK: stok negatif', function (&$c) {
                DB::table('produk')->where('id', $c['produk'])->update(['stok' => -1]);
            }, expectFail: true);

            $step('CHECK: qty nota_item = 0', function (&$c) {
                DB::table('nota_item')->insert([
                    'nota_id' => $c['nota'], 'produk_id' => $c['produk'], 'harga_satuan' => 1, 'qty' => 0,
                ]);
            }, expectFail: true);

            $step('FK: produk dengan kategori_id tidak ada', function (&$c) {
                DB::table('produk')->insert([
                    'nama' => 'Yatim', 'kategori_id' => 32767, 'satuan_id' => $c['satuan'], 'harga_jual' => 1, 'stok' => 0,
                ]);
            }, expectFail: true);
        } finally {
            DB::rollBack();
        }

        $step2 = ['name' => 'Rollback: data uji benar-benar hilang'];
        try {
            $sisa = DB::table('kategori_produk')->where('nama', 'like', 'Semen__tes_%')->count()
                  + DB::table('pengguna')->where('username', 'like', 'tes\_%')->count();
            $step2 += $sisa === 0
                ? ['ok' => true, 'detail' => 'Tidak ada sisa data uji.']
                : ['ok' => false, 'detail' => "$sisa baris sisa. Cek manual tabel kategori_produk / pengguna."];
        } catch (Throwable $e) {
            $step2 += ['ok' => false, 'detail' => strtok($e->getMessage(), "\n")];
        }
        $steps[] = $step2 + ['ms' => 0];

        return response()->json([
            'steps'    => $steps,
            'passed'   => count(array_filter($steps, fn ($s) => $s['ok'])),
            'total'    => count($steps),
            'total_ms' => round((microtime(true) - $start) * 1000, 1),
        ]);
    }
}
