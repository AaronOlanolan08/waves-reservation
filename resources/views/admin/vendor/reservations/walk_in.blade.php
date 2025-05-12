@extends('admin.vendor.reservation')

@section('styles')
  <link rel="stylesheet" href="{{ asset('css/walkin.css') }}" />  
@endsection

@section('reservation-content')
    <a href="{{ route('admin.vendor.reservation_calendar') }}" class="back-link">
        <span class="chevron-left"></span> Back to Calendar
    </a>
    <h2 class="head-title">Walk-in Reservation</h2>

    <section class="booking">
      <form action="{{ route('admin.vendor.walk_in.store') }}" method="POST" class="booking-form" onsubmit="return validateSelection()">
        @csrf
        
        <h2>Customer Reservation</h2>
        <p>7:00 AM - 9:00 PM</p>

        <div class="date-time-container">
			<div>
				<label for="customerName" class="payment-label">Customer Name:</label>
				<input type="text" name="name" id="customerName" required>
			 </div>
			 <div>
				<label for="customerContactNumber" class="payment-label">Contact number:</label>
				<input type="tel" pattern="[0-9]{11}" name="number" id="customerContactNumber" placeholder="09123456789" required>
			 </div>

          <div class="reservation-date">
            <label>Reservation Date:</label>
            <input type="date" name="date" id="date" required>
          </div>

          <div class="start-end-time-container">
            <div>
                <label for="startTime">Start Time</label>
                <input type="time" id="startTime" name="startTime" min="07:00" max="21:00" required>
            </div>
            <div>
                <label for="endTime">End Time</label>
                <input type="time" id="endTime" name="endTime" min="07:00" max="21:00" required>
            </div>
          </div>

		  <div class="flex space-x-4 mb-4">

			<!-- Cottage Dropdown -->
			<div class="dropdown relative inline-block w-full">
				 <button type="button" class="dropdown-btn w-full bg-white border border-gray-300 px-4 py-2 rounded shadow">
					  Select Cottages
				 </button>
				 <div id="cottage-menu" class="dropdown-menu absolute z-10 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-gray-300 rounded shadow hidden">
					@if (isset($cottages))  
						@foreach ($cottages as $cottage)
								@if ($cottage->is_active)
									<label for="cottage-{{ $cottage->id }}" class="flex items-center hover:bg-gray-100">
										<input class="mr-2 w-4 h-4" type="checkbox" name="cottages[]" value="{{ $cottage->id }}" id="cottage-{{ $cottage->id }}">
										{{ $cottage->name }} - ₱{{ number_format($cottage->price, 2) }}
									</label>
								@endif
						@endforeach
					  @endif
				 </div>
			</div>
	  
			<!-- Table Dropdown -->
			<div class="dropdown relative inline-block w-full">
				 <button type="button" class="dropdown-btn w-full bg-white border border-gray-300 px-4 py-2 rounded shadow">
					  Select Tables
				 </button>
				 <div id="table-menu" class="dropdown-menu absolute z-10 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-gray-300 rounded shadow hidden">
					@if (isset($tables))    
						@foreach ($tables as $table)
								@if ($table->is_active)
									<label for="table-{{ $table->id }}" class="flex items-center px-4 py-2 hover:bg-gray-100">
										<input type="checkbox" name="tables[]" value="{{ $table->id }}" id="table-{{ $table->id }}">
										{{ $table->name }} - ₱{{ number_format($table->price, 2) }}
									</label>
								@endif
						@endforeach
                    @endif
				</div>
			</div>
	  </div>
      <!-- Error Message Container -->
            <div id="error-message" class="text-red-600 text-sm mb-2 hidden"></div>
                <!-- Error messages will be displayed here -->
            </div>
        <div class="button-wrapper">
          <button type="submit" id="paymentButton" class="payment-button">Proceed to payment</button>
        </div>
      </form>
    </section>
@endsection

@section('scripts')
    <script>
        // Validation before form submission
        function validateSelection() {
            const cottages = document.querySelectorAll('input[name="cottages[]"]:checked');
            const tables = document.querySelectorAll('input[name="tables[]"]:checked');
            const button = document.getElementById("paymentButton");
            const errorMessageContainer = document.getElementById("error-message");
            const cottageChecked = cottages.length > 0;
            const tableChecked = tables.length > 0;

            const startTime = document.getElementById("startTime").value;
            const endTime = document.getElementById("endTime").value;
            let errorMessages = [];

            // Validate if start and end times are within the allowed range
            if (startTime < "07:00" || startTime > "21:00") {
                errorMessages.push("Start time must be between 07:00 AM and 09:00 PM.");
            }

            if (endTime < "07:00" || endTime > "21:00") {
                errorMessages.push("End time must be between 07:00 AM and 09:00 PM.");
            }

            // Validate if end time is after start time
            if (endTime <= startTime && endTime !== "") {
                errorMessages.push("End time must be later than the start time.");
            }

            // If neither cottage nor table is selected
            if (!cottageChecked && !tableChecked) {
                errorMessages.push("Please select at least one Cottage or Table before submitting.");
            }

            // If there are any error messages, display them
            if (errorMessages.length > 0) {
                errorMessageContainer.style.display = "block"; // Show the error message container
                errorMessageContainer.innerHTML = errorMessages.join("<br>"); // Display all error messages
                return false; // Prevent form submission
            }

            // 🔒 Disable the button to prevent multiple submissions
            if (button) {
                button.disabled = true;
                button.textContent = "Processing..."; // Optional
            }

            // If no errors, hide the error message container
            errorMessageContainer.style.display = "none";
            return true; // Allow form submission
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Function to update the minimum date and start time dynamically
            function updateDateAndTime() {
                // Adjust for (UTC +8)
                const now = new Date();
                const timezoneOffset = 8 * 60;
                const localDate = new Date(now.getTime() + timezoneOffset * 60000);
                const currentDate = localDate.toISOString().split('T')[0];
                const currentTime = localDate.toTimeString().split(' ')[0].slice(0, 5); // Get current time in HH:MM format

                // Update the minimum date to today
                const dateInput = document.getElementById("date");
                dateInput.setAttribute("min", currentDate);

                // If the selected date is in the past, reset it to today
                if (!dateInput.value || dateInput.value < currentDate) {
                    dateInput.value = currentDate;
                }
            }

            // Set the minimum date and start time on page load
            updateDateAndTime();

            // Update the date and time dynamically every minute
            setInterval(updateDateAndTime, 60000); // Check every 60 seconds

            // Fetch and update available amenities for the default date (today)
            fetchAvailableAmenities(getSelectedDate(), getStartTime(), getEndTime());

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
                return document.getElementById("startTime").value || "00:00";
            }

            // Helper function to get the selected end time
            function getEndTime() {
                return document.getElementById("endTime").value || "23:59";
            }
        });
  </script>
@endsection