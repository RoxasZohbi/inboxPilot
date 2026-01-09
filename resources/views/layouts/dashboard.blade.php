<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Dashboard')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 1.5rem;
        }
        .header nav a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
        }
        .main-wrapper {
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #f4f4f4;
            min-height: calc(100vh - 60px);
            padding: 1rem 0;
        }
        .sidebar a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #e0e0e0;
        }
        .sidebar a.active {
            background-color: #333;
            color: white;
        }
        .container {
            flex: 1;
            padding: 2rem;
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="header">
    <h1>Dashboard</h1>
    <nav>
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" style="background: none; border: none; color: white; cursor: pointer; margin-left: 1rem;">Logout</button>
        </form>
    </nav>
</div>

<div class="main-wrapper">
    <div class="sidebar">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>

        <!-- Add more menu items here -->
    </div>

    <div class="container">
        @yield('content')
    </div>
</div>

<script
  src="https://code.jquery.com/jquery-3.7.1.min.js"
  integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
  crossorigin="anonymous"></script>
@stack('scripts')

</body>
</html>
