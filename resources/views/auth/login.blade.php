<!DOCTYPE html>
<html>

<head>
    <title>Login - Waves Beach Resort</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/signup-link.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Jaldi&family=Allura&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Actor&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1 class="title">WAVES</h1>
        <h2 class="subtitle">Beach Resort</h2>

        @if (session('success'))
            <div class="alert success-alert">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf <!-- Include CSRF token for security -->

            @if ($errors->any())
                <div class="error-messages">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p class="signup-text">Don't have an account? <a href="{{ route('register') }}" class="signup-link">Sign Up</a>
        </p>
    </div>
</body>

</html>