<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="{{ asset('css/amenities.css') }}">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Amenities</title>
</head>

<body>
  <!-- NAVIGATION BAR SECTION -->

  <nav class="navbar">

    <div class="left-side-nav">
      <a href="{{ route('admin.dashboard') }}">
        <button class="dashboard" id="dashboard">
          <i class="material-icons nav-icons">dashboard</i> Dashboard
        </button>
      </a>
      <a href="{{ route('admin.vendor.amenities', ['type' => 'cottage']) }}">
        <button class="ameneties" id="ameneties-btn">
          <i class="material-icons nav-icons">holiday_village</i> Amenities
        </button>
      </a>
      <a href="{{ route('admin.vendor.reservation_calendar') }}">
        <button class="reservations" id="reservation">
          <i class="material-icons nav-icons">date_range</i> Reservations
        </button>
      </a>
    </div>

    <div class="right-side-nav">
      <a href="{{ route('admin.vendor.profile') }}">
        <button class="profile">
          <i class="material-icons" style="font-size:45px; color: white">
            account_circle
          </i>
        </button>
      </a>
    </div>

  </nav>

  <!-- SIDEBAR SECTION  -->

  <div class="container">

    <nav class="sidebar">
      <div class="icon-container" id="cottages-tab">
        <i class="material-icons side-icons">
          storefront
        </i>
        <p class="icon-label">
          Cottages
        </p>
      </div>

      <div class="icon-container" id="tables-tab">
        <i class="material-icons side-icons">
          dining
        </i>
        <p class="icon-label">
          Tables
        </p>
      </div>

      <div class="icon-container" id="cancel-tab">
        <i class="material-icons side-icons">block</i>
        <p class="icon-label">Cancelled</p>
      </div>

    </nav>

    <!----------------------- MAIN SECTION  ---------------------------->

    <main class="main">

      <div class="date-container">
        <h2 id="current-date" class="date-title"></h2>
        <div class="date-selectors">
          <select id="month-select"></select>
          <select id="day-select"></select>
          <select id="year-select"></select>
          <input class="px-4 py-2 border rounded-md outline-none" type="time" id="time-select">
        </div>
      </div>

      @yield('amenities-content')


  </div>



  <!-- JavaScript Section -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const currentDateEl = document.getElementById('current-date');
      const monthSelect = document.getElementById('month-select');
      const daySelect = document.getElementById('day-select');
      const yearSelect = document.getElementById('year-select');
      const timeSelect = document.getElementById('time-select');
      const amenitiesTableBody = document.querySelector('.availability-table tbody');
      const now = new Date();

      const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
      ];

      // Populate month select
      monthNames.forEach((month, index) => {
        const option = document.createElement('option');
        option.value = index;
        option.textContent = month;
        monthSelect.appendChild(option);
      });

      const currentYear = new Date().getFullYear();
      for (let y = currentYear - 5; y <= currentYear + 5; y++) {
        const option = document.createElement('option');
        option.value = y;
        option.textContent = y;
        yearSelect.appendChild(option);
      }

      function updateCurrentDateTitle(year, month, day) {
        const parsedYear = parseInt(year, 10);
        const parsedMonth = parseInt(month, 10) - 1;
        const parsedDay = parseInt(day, 10);

        const date = new Date(parsedYear, parsedMonth, parsedDay);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

        if (isNaN(date)) {
          currentDateEl.textContent = 'Invalid Date';
        } else {
          currentDateEl.textContent = date.toLocaleDateString(undefined, options);
        }
      }

      function updateCurrentDateTitle(year, month, day) {
        const date = new Date(year, month, day);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        currentDateEl.textContent = date.toLocaleDateString(undefined, options);
      }


      function updateDays() {
        const selectedYear = parseInt(yearSelect.value);
        const selectedMonth = parseInt(monthSelect.value);
        const daysInMonth = new Date(selectedYear, selectedMonth + 1, 0).getDate();

        daySelect.innerHTML = '';
        for (let d = 1; d <= daysInMonth; d++) {
          const option = document.createElement('option');
          option.value = d;
          option.textContent = d;
          daySelect.appendChild(option);
        }
      }

      function updateAmenitiesStatus() {
        const selectedYear = yearSelect.value;
        const selectedMonth = parseInt(monthSelect.value) + 1;
        const selectedDay = daySelect.value;
        const selectedTime = timeSelect.value;

        if (!selectedYear || !selectedMonth || !selectedDay || !selectedTime) return;

        const dateTime = `${selectedYear}-${selectedMonth.toString().padStart(2, '0')}-${selectedDay.toString().padStart(2, '0')}T${selectedTime}`;

        fetch(`{{ url()->current() }}?date_time=${dateTime}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(response => response.json())
          .then(data => {
            const amenities = data.amenities;

            const tableBody = document.querySelector('.availability-table tbody');
            tableBody.innerHTML = '';

            amenities.forEach(amenity => {
              const row = document.createElement('tr');
              row.className = amenity.availability_status === 'Available' ? 'active' : 'archived';
              row.innerHTML = `
                    <td>${amenity.name}</td>
                    <td>₱${parseFloat(amenity.price).toFixed(2)}</td>
                    <td>
                        <span class="${amenity.availability_status === 'Available' ? 'text-green-600' : 'text-red-600'}">
                            ${amenity.availability_status}
                        </span>
                    </td>
                `;
              tableBody.appendChild(row);
            });
          })
          .catch(error => {
            console.error('Error fetching amenities:', error);
          });
      }

      // Initial setup
      monthSelect.value = now.getMonth();
      yearSelect.value = now.getFullYear();
      updateDays();
      daySelect.value = now.getDate();
      updateCurrentDateTitle(now.getFullYear(), now.getMonth(), now.getDate());

      // Set current time to timeSelect (HH:MM)
      const hours = now.getHours().toString().padStart(2, '0');
      const minutes = now.getMinutes().toString().padStart(2, '0');
      timeSelect.value = `${hours}:${minutes}`;

      monthSelect.addEventListener('change', () => {
        updateDays();
        updateCurrentDateTitle(yearSelect.value, monthSelect.value, daySelect.value);
        updateAmenitiesStatus();
      });
      yearSelect.addEventListener('change', () => {
        updateDays();
        updateCurrentDateTitle(yearSelect.value, monthSelect.value, daySelect.value);
        updateAmenitiesStatus();
      });
      daySelect.addEventListener('change', () => {
        updateAmenitiesStatus();
        updateCurrentDateTitle(yearSelect.value, monthSelect.value, daySelect.value);
      });
      timeSelect.addEventListener('change', updateAmenitiesStatus);
    });

    // Sidebar tab functionality
    const cottagesTab = document.getElementById('cottages-tab');
    const tablesTab = document.getElementById('tables-tab');
    const cancelTab = document.getElementById('cancel-tab');

    cottagesTab.addEventListener('click', () => {
      setActiveTab(cottagesTab);
      window.location.href = "{{ route('admin.vendor.amenities', ['type' => 'cottage']) }}";
    });

    tablesTab.addEventListener('click', () => {
      setActiveTab(tablesTab);
      window.location.href = "{{ route('admin.vendor.amenities', ['type' => 'table']) }}";
    });

    cancelTab.addEventListener('click', () => {
      setActiveTab(cancelTab);
      window.location.href = "{{ route('admin.vendor.cancel') }}";
    });

    function setActiveTab(activeTab) {
      document.querySelectorAll('.icon-container').forEach(tab => {
        tab.classList.remove('active');
      });
      activeTab.classList.add('active');
    }
  </script>
</body>

</html>