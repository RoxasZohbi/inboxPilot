<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>

<h1>Forgot Password</h1>

<p>Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div>
        <label>Email</label><br>
        <input type="email" name="email" required>
    </div>

    <button type="submit">Email Password Reset Link</button>
</form>

</body>
</html>
