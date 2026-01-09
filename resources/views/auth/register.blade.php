<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h1>Register</h1>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div>
        <label>Name</label><br>
        <input type="text" name="name" required>
    </div>

    <div>
        <label>Email</label><br>
        <input type="email" name="email" required>
    </div>

    <div>
        <label>Password</label><br>
        <input type="password" name="password" required>
    </div>

    <div>
        <label>Confirm Password</label><br>
        <input type="password" name="password_confirmation" required>
    </div>

    <button type="submit">Register</button>
</form>

</body>
</html>
