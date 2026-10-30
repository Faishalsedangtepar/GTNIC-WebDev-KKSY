<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus>
            @error('username') <p style="color:red">{{ $message }}</p> @enderror
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Masuk</button>
    </form>
</body>
</html>