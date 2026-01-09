<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h1>Login</h1>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div>
        <label>Email</label><br>
        <input type="email" name="email" required autofocus>
    </div>

    <div>
        <label>Password</label><br>
        <input type="password" name="password" required>
    </div>

    <div>
        <label>
            <input type="checkbox" name="remember">
            Remember me
        </label>
    </div>

    <div>
        <a href="{{ route('password.request') }}">Forgot your password?</a>
    </div>

    <button type="submit">Login</button>
</form>

</body>
</html>
