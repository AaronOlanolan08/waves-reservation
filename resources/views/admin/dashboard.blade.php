<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="{{ asset('css/admin_dashboard.css') }}">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <title>Dashboard</title>
</head>

<body>

  <!-- NAVIGATION BAR SECTION -->
  <nav class="navbar">
    @php
    $user = \App\Models\Admin::find(Auth::id());
    $calendar_route = $user && $user->role === 'Manager' ? route('admin.reservation.list') : route('admin.vendor.reservation_calendar');
    $amenities_route = $user && $user->role === 'Manager' ? route('admin.manager.amenities', ['type' => 'cottage']) : route('admin.vendor.amenities', ['type' => 'cottage']);
  @endphp

    <div class="left-side-nav">
      <a href="{{ route('admin.dashboard') }}">
        <button class="dashboard" id="dashboard">
          <i class="material-icons nav-icons">dashboard</i> Dashboard
        </button>
      </a>
      <a href="{{ $amenities_route }}">
        <button class="ameneties" id="ameneties">
          <i class="material-icons nav-icons">holiday_village</i> Amenities
        </button>
      </a>
      <a href="{{ $calendar_route }}">
        <button class="reservations" id="reservation">
          <i class="material-icons nav-icons">date_range</i> Reservations
        </button>
      </a>
    </div>

    <div class="right-side-nav">
      @if($user->role === 'Manager')
      <a href="{{ route('admin.manager.profile') }}">
      <button class="profile">
        <i class="material-icons" style="font-size:45px; color: white">account_circle</i>
      </button>
      </a>
    @elseif($user->role === 'Vendor')
      <a href="{{ route('admin.vendor.profile') }}">
      <button class="profile">
        <i class="material-icons" style="font-size:45px; color: white">account_circle</i>
      </button>
      </a>
    @endif
    </div>
  </nav>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- MAIN SECTION -->
  <main class="main">

    <!-- Revenue Section -->
    <div class="revenue-section">
      <div class="revenue-cards">
        <div class="card">
          <h3>Revenue for the Month</h3>
          <p class="amount">₱{{ number_format($revenue, 2) }}</p>
        </div>
        <div class="card">
          <h3>Total Annual Revenue</h3>
          <p class="amount">₱{{ number_format($annualRevenue, 2) }}</p>
        </div>
        <div class="card">
          <h3>Average Monthly Revenue</h3>
          <p class="amount">₱{{ number_format($averageMonthlyRevenue, 2) }}</p>
        </div>
      </div>
    </div>

    <!-- Reservations Section -->
    <div class="reservations-section">
      <div class="reservation-cards">
        <div class="card">
          <h3>Completed Reservations</h3>
          <p class="amount">{{ $completedReservations }}</p>
        </div>
        <div class="card">
          <h3>Pending Reservations</h3>
          <p class="amount">{{ $pendingReservations }}</p>
        </div>
        <div class="card">
          <h3>Verified Reservations</h3>
          <p class="amount">{{ $verifiedReservations }}</p>
        </div>
      </div>
    </div>


    <!-- Graphs Section -->
    <div class="graph-section">
      <div class="chart-container">
        <canvas id="revenueChart"></canvas>
        <canvas id="monthlyRevenueChart"></canvas>
      </div>
    </div>



    <script>
      // Reservation Bar Chart
      var ctx = document.getElementById('revenueChart').getContext('2d');
      var revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Completed', 'Pending', 'Verified'],
          datasets: [
            {
              label: 'Completed',
              data: [{{ $completedReservations }}, 0, 0],
              backgroundColor: '#36A2EB'
            },
            {
              label: 'Pending',
              data: [0, {{ $pendingReservations }}, 0],
              backgroundColor: '#FFCE56'
            },
            {
              label: 'Verified',
              data: [0, 0, {{ $verifiedReservations }}],
              backgroundColor: '#47ff75'
            }
          ]
        },

        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

      // Monthly Revenue Line Chart
      var revenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
      var monthlyRevenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
          labels: {!! json_encode($monthlyLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!},
          datasets: [{
            label: 'Monthly Revenue',
            data: {!! json_encode($monthlyRevenue ?? array_fill(0, 12, 0)) !!},
            fill: false,
            borderColor: '#4CAF50',
            tension: 0.1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    </script>

  </main>

</body>

</html>