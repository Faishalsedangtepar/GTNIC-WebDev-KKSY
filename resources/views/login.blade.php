<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow" style="width: 400px;">
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
        </div>
    </div>
</body>
</html>
