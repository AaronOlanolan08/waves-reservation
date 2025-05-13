<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waves Beach Resort</title>
    <link rel="stylesheet" href="{{ asset('css/res.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Jaldi&family=Allura&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>
    <header>
        <div class="logo">
            <img src="{{ asset('images/logs.png') }}" alt="WAVES Logo">
            <h1 class="title">WAVES <span>Resort</span></h1>
        </div>
        <nav>
            <a href="{{ route('customer.about') }}">About</a>
            <a href="{{ route('customer.reservation') }}">Book</a>

            <div class="profile-container">
                <a href="{{ route('customer.profile') }}">
                    <i class="fas fa-user-circle" id="profile-icon" style="font-size: 32px; cursor: pointer;"></i>
                </a>
                <div class="dropdown-menu" id="dropdown-menu">
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="welcome-text">
            <p>Welcome to WAVES Resort! Spend your day lounging in our private cabanas, taking a dip in the
                crystal-clear waters, or enjoying a refreshing cocktail at our beachside bar.</p>
            <br><br>
            We are committed to providing a welcoming and relaxing environment where you can connect with nature,
            recharge, and make lasting memories.
            <br><br>
            Our friendly staff is dedicated to ensuring your visit is comfortable and enjoyable, with personalized
            service that makes you feel right at home.
        </section>
    </main>
</body>

</html>