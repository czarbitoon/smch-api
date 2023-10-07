<!-- Add this link in the head section of your layout -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your App Title</title>
    <!-- Include any CSS files you need -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <header>
        <!-- Include your navigation menu or any other header content here -->
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('devices') }}">Devices</a></li>
                <li><a href="{{ route('reports') }}">Reports</a></li>
                <!-- Add more links as needed -->
                <li class="dropdown">
                    <a href="#" class="dropbtn">Profile</a>
                    <div class="dropdown-content">
                        <a href="{{ route('profile') }}">View Profile</a>
                        <a href="{{ route('logout') }}">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>

    </header>

    <main>
        <!-- This is where your page-specific content will go -->
        @yield('content')
    </main>

    <footer>
        <!-- Include your footer content here -->
    </footer>

    <!-- Include any JavaScript files you need -->
    <script src="{{ asset('js/scripts.js') }}"></script>
</body>
</html>
