<!DOCTYPE html>
<html>

<head>
    <title>Sign Up - Waves Beach Resort</title>
    <link rel="stylesheet" href="{{ asset('css/signup.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Jaldi&family=Allura&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Actor&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1 class="title">WAVES</h1>
        <h2 class="subtitle">Beach Resort</h2>
        <form method="POST" action="{{ route('cust.register') }}">
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
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="tel" name="number" placeholder="Contact Number" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <p class="login-text">Already have an account? <a href="{{ route('login') }}" class="login-link">Login</a></p>
    </div>
</body>

</html>