<h1>Dashboard</h1>
<p>Halo, {{ auth()->user()->username }} ({{ auth()->user()->role }})</p>
<p>halaman kasir</p>
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>