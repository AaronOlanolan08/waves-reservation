<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waves Beach Resort</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- bootstrap links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- bootstrap links -->
    <!-- fonts links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jaldi&family=Allura&display=swap" rel="stylesheet">
    <!-- fonts links -->
</head>
<body>
  <div class="all-content">
   
  <!-- navbar -->
   
  <nav class="navbar navbar-expand-lg" id="navbar">
    <div class="logo">
      <img src="{{ asset('images/logs.png') }}" alt="WAVES Logo">
      <h1 class="title">WAVES</h1>
      <h2 class="sub-title">Resort</h2>
  </div>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span><i class="fa-solid fa-bars" style="color: white; font-size: 23px;"></i></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">About</a>
          </li>
        
        <li class="nav-item">
    @guest
        <!-- If the user is not logged in, redirect to login page -->
        <a class="nav-link" href="{{ route('reservation') }}">Book</a>
    @endguest

    @auth
        <!-- If the user is logged in, redirect to reservation page -->
        <a class="nav-link" href="{{ route('customer.reservation') }}">Book</a>
    @endauth
</li>

           <div class="profile-container">
               <a href="{{ route('customer.profile') }}">
                     <i class="fas fa-user-circle" id="profile-icon" style="font-size: 32px; cursor: pointer;"></i>
         
      </div>
    </div>
  </nav>
  <!-- navbar -->

<!-- home section -->
   <section id="home">
    <div class="content">
      <h3>Start Your Day With a <br> Fresh Coffee</h3>
      <p>Feel you summer and relaxing moment with your family and friends
         <br>It's time to feel the summer vibe.
      </p>
      <button id="btn">Book Now</button>
    </div>
   </section>

<!-- about section -->
<div class="about" id="about">
  <div class="container">
  <div class="heading">About Us</div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <img src="{{ asset('images/abt.jpg') }}" alt="">
        </div>
      </div>
      <div class="col-md-6">
       <p>Welcome to WAVES Resort! Spend your day lounging in our private cabanas, taking a dip in the crystal-clear waters, or enjoying a refreshing cocktail at our beachside bar.</p>          
          <br><br>We are committed to providing a welcoming and relaxing environment where you can connect with nature, recharge, and make lasting memories. 
          <br><br>Our friendly staff is dedicated to ensuring your visit is comfortable and enjoyable, with personalized service that makes you feel right at home.
         </p>
      </div>
    </div>
  </div>
</div>
<!-- about section -->


<section class="top-cards py-5">
  <div class="heading2">Resort Preview</div>
  <div class="container">
    <div class="row g-4"> <!-- g-4 adds gutter spacing -->
      <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card h-100">
          <img src="{{ asset('images/prev1.jpg') }}" class="img-fluid" alt="Preview 1">
        </div>
      </div>
      <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card h-100">
          <img src="{{ asset('images/prev2.jpg') }}" class="img-fluid" alt="Preview 2">
        </div>
      </div>
      <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card h-100">
          <img src="{{ asset('images/prev3.jpg') }}" class="img-fluid" alt="Preview 3">
        </div>
      </div>
    </div>
  </div>
</section>



   <!-- footer -->
  
    <footer class="site-footer">
    <p>&copy; 2025 Waves Beach Resort. All rights reserved.</p>
</footer>

  </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>
</html>