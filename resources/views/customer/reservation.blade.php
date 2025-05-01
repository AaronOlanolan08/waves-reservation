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
            <h1 class="title">WAVES <span>Beach Resort</span></h1>
        </div>
        <nav>
            <a href="#">Book Now</a>
            <a href="#">About Us</a>
            <div class="profile-container">
                <a href="{{ route('customer.profile') }}">
                    <i class="fas fa-user-circle" id="profile-icon" style="font-size: 32px; cursor: pointer;"></i>
                </a>
                <div class="dropdown-menu" id="dropdown-menu">
                </div>
            </div>
        </nav>
    </header>

    <section class="booking">
        <div class="booking-form">
            <h2>Enjoy Your Vacation</h2>
            <p>7:00 AM - 9:00 PM</p>
            @if(session('success'))
                <div style="text-xs color: green;">{{ session('success') }}</div>
            @endif
            <form action="{{ route('reservation.store') }}" method="POST" onsubmit="return validateSelection()">
                @csrf

                <!-- Date picker -->
                <div class="date-time-container">
                    <label for="date">Reservation Date</label>
                    <input type="date" id="date" name="date" required>
                </div>

                <!-- Start time picker -->
                <div class="start-end-time-container">
                    <div>
                        <label for="startTime">Start Time</label>
                        <input type="time" id="startTime" name="startTime" required min="07:00" max="21:00">
                    </div>
                    <div>
                        <label for="endTime">End Time</label>
                        <input type="time" id="endTime" name="endTime" required min="07:00" max="21:00">
                    </div>
                </div>


                    <!-- Cottage selection with checkboxes -->
                    <div class="custom-dropdown" id="cottage-dropdown">
                        <button type="button" class="dropdown-btn">Cottage</button>
                        <div class="dropdown-menu" id="cottage-menu">
                            @foreach ($cottages as $cottage)
                                @if ($cottage->is_active)
                                    <label for="cottage-{{ $cottage->id }}">
                                        <input class="form-check-input p-2" type="checkbox" name="cottages[]"
                                            value="{{ $cottage->id }}" id="cottage-{{ $cottage->id }}">
                                        {{ $cottage->name }} - ₱{{ number_format($cottage->price, 2) }}
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                <!-- Table selection with checkboxes -->
                <div class="custom-dropdown" id="table-dropdown">
                    <button type="button" class="dropdown-btn">Table</button>
                    <div class="dropdown-menu" id="table-menu">
                        @foreach ($tables as $table)
                            @if ($table->is_active)
                                <label for="table-{{ $table->id }}">
                                    <input type="checkbox" name="tables[]" value="{{ $table->id }}" id="table-{{ $table->id }}">
                                    {{ $table->name }} - ₱{{ number_format($table->price, 2) }}
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Error Message Container -->
                <div id="error-message" style="color: red; font-size: 14px; display: none; margin-bottom: 10px;">
                    Please select at least one Cottage or Table before submitting.
                </div>

                <!-- Submit button -->
                <div class="button-wrapper">
                    <button class="payment-button">Proceed to Payment</button>
                </div>
            </form>
        </div>

        <!-- Image Carousel -->
        <div class="image-carousel">
            <button class="prev">&#10094;</button>
            <img src="{{ asset('images/beach2.jpg') }}" alt="Beach View">
            <button class="next">&#10095;</button>
        </div>
    </section>

<script>
    // Validation before form submission
    function validateSelection() {
        const cottages = document.querySelectorAll('input[name="cottages[]"]:checked');
        const tables = document.querySelectorAll('input[name="tables[]"]:checked');
        const cottageChecked = cottages.length > 0;
        const tableChecked = tables.length > 0;
        
        const startTime = document.getElementById("startTime").value;
        const endTime = document.getElementById("endTime").value;
        const errorMessageContainer = document.getElementById("error-message");
        
        // Validate if end time is after start time
        if (endTime <= startTime && endTime !== "") {
            alert("End time must be later than the start time.");
            return false; // Prevent form submission
        }

        // If neither cottage nor table is selected
        if (!cottageChecked && !tableChecked) {
            errorMessageContainer.style.display = "block"; // Show the error message
            return false; // Prevent form submission
        }

        // If either cottage or table is selected, hide the error message
        errorMessageContainer.style.display = "none";
        return true; // Allow form submission
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Set the minimum date to today for the date input field
        let today = new Date().toISOString().split('T')[0];
        document.getElementById("date").setAttribute("min", today);
        document.getElementById("date").value = today;

        // Fetch and update available amenities for the default date (today)
        fetchAvailableAmenities(today, getStartTime(), getEndTime());

        // Handle dropdown visibility
        document.querySelectorAll('.dropdown-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const dropdownMenu = this.nextElementSibling;

                if (dropdownMenu && dropdownMenu.classList) {
                    // Toggle visibility by checking the current state
                    if (dropdownMenu.style.display === "none" || dropdownMenu.classList.contains('hidden')) {
                        dropdownMenu.style.display = "block"; // Show the dropdown
                        dropdownMenu.classList.remove('hidden');
                    } else {
                        dropdownMenu.style.display = "none"; // Hide the dropdown
                        dropdownMenu.classList.add('hidden');
                    }
                }
            });
        });

        // Close dropdown if clicked outside
        document.addEventListener('click', function (event) {
            document.querySelectorAll('.dropdown').forEach(function (dropdown) {
                const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                if (!dropdown.contains(event.target) && dropdownMenu) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        });

        // Update minimum end time based on selected start time
        document.getElementById("startTime").addEventListener("change", function () {
            let startTime = this.value;
            document.getElementById("endTime").setAttribute("min", startTime);

            // Fetch updated amenities based on the new start and end times
            fetchAvailableAmenities(getSelectedDate(), getStartTime(), getEndTime());
        });

        document.getElementById("endTime").addEventListener("change", function () {
            // Fetch updated amenities based on the new end time
            fetchAvailableAmenities(getSelectedDate(), getStartTime(), getEndTime());
        });

        // Fetch and update available amenities when a date is selected
        document.getElementById("date").addEventListener("change", function () {
            let selectedDate = this.value;
            fetchAvailableAmenities(selectedDate, getStartTime(), getEndTime());
        });

        // Function to fetch and update available cottages and tables
        function fetchAvailableAmenities(date, startTime, endTime) {
            fetch(`/customer/check-availability?date=${date}&startTime=${startTime}&endTime=${endTime}`)
                .then(response => response.json())
                .then(data => {
                    updateAvailableAmenities(data.availableCottages, data.availableTables);
                })
                .catch(error => {
                    console.error("Error fetching availability data:", error);
                    updateAvailableAmenities([], []); // Clear dropdowns on error
                });
        }

        // Function to update the available cottages and tables dynamically
        function updateAvailableAmenities(cottages, tables) {
            // Check if cottages and tables are arrays
            if (!Array.isArray(cottages) || !Array.isArray(tables)) {
                return;
            }

            // Update Cottage Dropdown
            let cottageMenu = document.getElementById("cottage-menu");
            cottageMenu.innerHTML = ''; // Clear existing items

            if (cottages.length > 0) {
                cottages.forEach(cottage => {
                    let label = document.createElement('label');
                    label.innerHTML = `
                    <input type="checkbox" name="cottages[]" value="${cottage.id}" id="cottage-${cottage.id}">
                    ${cottage.name} - ₱${cottage.price.toFixed(2)}
                `;
                    cottageMenu.appendChild(label);
                });
            } else {
                cottageMenu.innerHTML = '<p class="text-gray-500 px-4 py-2">No cottages available</p>';
            }

            // Update Table Dropdown
            let tableMenu = document.getElementById("table-menu");
            tableMenu.innerHTML = ''; // Clear existing items

            if (tables.length > 0) {
                tables.forEach(table => {
                    let label = document.createElement('label');
                    label.innerHTML = `
                    <input type="checkbox" name="tables[]" value="${table.id}" id="table-${table.id}">
                    ${table.name} - ₱${table.price.toFixed(2)}
                `;
                    tableMenu.appendChild(label);
                });
            } else {
                tableMenu.innerHTML = '<p class="text-gray-500 px-4 py-2">No tables available</p>';
            }
        }

        // Helper function to get the selected date
        function getSelectedDate() {
            return document.getElementById("date").value;
        }

        // Helper function to get the selected start time
        function getStartTime() {
            return document.getElementById("startTime").value || "07:00";
        }

        // Helper function to get the selected end time
        function getEndTime() {
            return document.getElementById("endTime").value || "21:00";
        }

        // Image Carousel
        const images = [
            "{{ asset('images/beach1.jpg') }}",
            "{{ asset('images/beach2.jpg') }}",
            "{{ asset('images/beach3.jpg') }}",
        ];

        let currentIndex = 0;
        const imageElement = document.querySelector(".image-carousel img");
        const prevButton = document.querySelector(".prev");
        const nextButton = document.querySelector(".next");

        function updateImage() {
            imageElement.src = images[currentIndex];
        }

        prevButton.addEventListener("click", function () {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            updateImage();
        });

        nextButton.addEventListener("click", function () {
            currentIndex = (currentIndex + 1) % images.length;
            updateImage();
        });

        updateImage();
    });
</script>


</body>

</html>