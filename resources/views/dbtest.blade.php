<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tes Database · Kasir</title>
    <style>
        :root {
            --bg: #f6f7f9; --card: #fff; --text: #1a1d23; --muted: #6b7280; --line: #e5e7eb;
            --ok: #15803d; --ok-bg: #dcfce7; --bad: #b91c1c; --bad-bg: #fee2e2; --warn: #a16207; --warn-bg: #fef9c3;
            --accent: #2563eb;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0f1115; --card: #181b22; --text: #e6e8ec; --muted: #9aa1ad; --line: #2a2f3a;
                --ok: #4ade80; --ok-bg: #14301f; --bad: #f87171; --bad-bg: #3a1a1a; --warn: #facc15; --warn-bg: #3a3112;
                --accent: #60a5fa;
            }
        }
        * { box-sizing: border-box; }
        body { margin: 0; font: 15px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif; background: var(--bg); color: var(--text); }
        main { max-width: 980px; margin: 0 auto; padding: 24px 16px 64px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 16px; margin: 0 0 12px; }
        .sub { color: var(--muted); margin: 0 0 20px; }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 13px; font-weight: 600; }
        .ok { background: var(--ok-bg); color: var(--ok); }
        .bad { background: var(--bad-bg); color: var(--bad); }
        .warn { background: var(--warn-bg); color: var(--warn); }
        dl { display: grid; grid-template-columns: 140px 1fr; gap: 6px 12px; margin: 12px 0 0; }
        dt { color: var(--muted); }
        dd { margin: 0; word-break: break-all; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid var(--line); font-size: 14px; }
        th { color: var(--muted); font-weight: 500; }
        td.num { text-align: right; font-variant-numeric: tabular-nums; }
        button { font: inherit; font-weight: 600; background: var(--accent); color: #fff; border: 0; border-radius: 8px; padding: 10px 16px; cursor: pointer; }
        button:disabled { opacity: .6; cursor: wait; }
        .row { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .step { display: grid; grid-template-columns: 24px 1fr auto; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--line); }
        .step:last-child { border-bottom: 0; }
        .step .d { color: var(--muted); font-size: 13px; word-break: break-word; }
        .step .ms { color: var(--muted); font-size: 12px; font-variant-numeric: tabular-nums; }
        .err { white-space: pre-wrap; word-break: break-word; }
    </style>
</head>
<body>
<main>
    <h1>Tes Database Kasir</h1>
    <p class="sub">Halaman uji koneksi & skema. Hanya aktif di <code>APP_ENV=local</code>. Hapus sebelum deploy.</p>

    {{-- 1. Koneksi --}}
    <section class="card">
        <div class="row">
            <h2 style="margin:0">Koneksi</h2>
            @if ($status['connected'])
                <span class="badge ok">Terhubung</span>
            @else
                <span class="badge bad">Gagal</span>
            @endif
        </div>

        @unless ($status['connected'])
            <p class="err" style="color:var(--bad)">{{ $status['error'] ?? 'Tidak diketahui' }}</p>
        @endunless

        <dl>
            <dt>Driver</dt><dd>{{ $status['driver'] }}</dd>
            <dt>Host : Port</dt><dd>{{ $status['host'] }}:{{ $status['port'] }}</dd>
            <dt>Database</dt><dd>{{ $status['database'] }}</dd>
            <dt>User</dt><dd>{{ $status['username'] }}</dd>
            <dt>SSL mode</dt><dd>{{ $status['sslmode'] }}</dd>
            @if ($status['connected'])
                <dt>Versi server</dt><dd>{{ $status['version'] }}</dd>
                <dt>Waktu server</dt><dd>{{ $status['server_now'] }}</dd>
                <dt>Waktu konek</dt><dd>{{ $status['connect_ms'] }} ms</dd>
                <dt>Ping (1 query)</dt>
                <dd>
                    {{ $status['ping_ms'] }} ms
                    @if ($status['ping_ms'] > 150)
                        <span class="badge warn">lambat</span>
                    @endif
                </dd>
            @endif
        </dl>

        @if ($status['connected'] && $status['ping_ms'] > 150)
            <p style="color:var(--warn);margin:12px 0 0;font-size:14px">
                Satu query makan &gt;150 ms. Kalau pooler-nya di region jauh, tiap request Laravel
                (apalagi dengan session/cache driver <code>database</code>) ikut lambat.
            </p>
        @endif
    </section>

    {{-- 2. Skema --}}
    @if ($status['connected'])
        <section class="card">
            <h2>Skema</h2>
            <table>
                <thead><tr><th>Nama</th><th>Jenis</th><th>Status</th><th class="num">Baris</th></tr></thead>
                <tbody>
                @foreach ($status['objects'] as $o)
                    <tr>
                        <td><code>{{ $o['name'] }}</code></td>
                        <td>{{ $o['kind'] }}</td>
                        <td>
                            @if ($o['present'])
                                <span class="badge ok">ada</span>
                            @else
                                <span class="badge bad">belum ada</span>
                            @endif
                        </td>
                        <td class="num">{{ $o['count'] ?? '–' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if (collect($status['objects'])->contains(fn ($o) => ! $o['present']))
                <p style="color:var(--bad);margin:12px 0 0;font-size:14px">
                    Ada objek yang belum ada. Jalankan <code>php artisan migrate</code>.
                </p>
            @endif
        </section>

        {{-- 3. Smoke test --}}
        <section class="card">
            <div class="row">
                <h2 style="margin:0">Tes CRUD & constraint</h2>
                <button id="run" type="button">Jalankan tes</button>
                <span id="summary" class="sub" style="margin:0"></span>
            </div>
            <p class="sub" style="margin:8px 0 0;font-size:13px">
                Insert, baca view, kolom generated, UNIQUE/CHECK/FK, lalu ROLLBACK. Tidak ada data yang tersimpan.
            </p>
            <div id="results" style="margin-top:12px"></div>
        </section>
    @endif
</main>

<script>
(() => {
    const btn = document.getElementById('run');
    if (!btn) return;
    const out = document.getElementById('results');
    const summary = document.getElementById('summary');
    const el = (tag, cls, text) => {
        const n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text !== undefined) n.textContent = text;
        return n;
    };

    btn.addEventListener('click', async () => {
        btn.disabled = true;
        out.replaceChildren();
        summary.textContent = 'Menjalankan…';
        try {
            const res = await fetch(@json(url('/db-test/run')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            data.steps.forEach(s => {
                const row = el('div', 'step');
                row.append(el('span', null, s.ok ? '✅' : '❌'));
                const body = el('div');
                body.append(el('div', null, s.name), el('div', 'd', s.detail));
                row.append(body, el('span', 'ms', s.ms + ' ms'));
                out.append(row);
            });

            summary.textContent = `${data.passed}/${data.total} lolos · ${data.total_ms} ms`;
            summary.style.color = data.passed === data.total ? 'var(--ok)' : 'var(--bad)';
        } catch (e) {
            summary.textContent = 'Gagal menjalankan tes: ' + e.message;
            summary.style.color = 'var(--bad)';
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
</body>
</html>
