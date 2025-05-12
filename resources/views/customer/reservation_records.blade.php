<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WAVES Beach Resort</title>
    <link rel="stylesheet" href="{{ asset('css/reservation_records.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jaldi&family=Allura&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="back-button">
            <a href="{{ route('customer.reservation') }}" class="back-btn">
                <i class="fas fa-chevron-left"></i>
                <span>Back to main</span>
            </a>
        </div>

        <div class="customer-profile">
            <div class="avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <span class="customer-name">{{ $customer->name }}</span>
        </div>

        <div class="menu">
            <a href="{{ route('customer.profile') }}">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="{{ route('customer.reservation.records') }}" class="active">
                <i class="fas fa-calendar-alt"></i>
                <span>Reservation</span>
            </a>

        </div>

        <!-- logout -->
        <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf <!-- CSRF Token for security -->
        </form>

        <button id="logoutButton" class="logout">
            <i class="fas fa-sign-out-alt"></i> Log Out
        </button>

        <script>
            document.getElementById('logoutButton').addEventListener('click', function () {
                document.getElementById('logoutForm').submit();
            });
        </script>
    </div>

    <div class="reservations-container">
        <div class="content-header">
            <h2>Your Reservations</h2>
            <div class="status-tabs">
                <button class="status-tab active" data-status="current">Current</button>
                <button class="status-tab" data-status="pending">Pending</button>
                <button class="status-tab" data-status="completed">Completed</button>
                <button class="status-tab" data-status="cancelled">Cancelled/Invalid</button>
            </div>
            
            <div class="status-legend">
                <span class="legend-item" style="--color: blue;">Fully Paid</span>
                <span class="legend-item" style="--color: lightgreen;"> Partially Paid</span>
                <span class="legend-item" style="--color: orange;"> With Downpayment</span>
                <span class="legend-item" style="--color: yellow;"> No Downpayment</span>
                <span class="legend-item" style="--color: red;">Cancelled/Invalid</span>
                <span class="legend-item" style="--color: rgb(51, 51, 51);">Past</span>
            </div>
        </div>
    
        <div class="reservations-content max-h-[500px] overflow-y-auto">
            <!-- Current Reservations -->
            <div class="reservation-list active" id="current-reservations">
                @forelse ($paidReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
                @php
                    $statusColor = match ($reservation->bill->status) {
                        'paid' => 'blue',
                        'partially paid' => 'lightgreen',
                    };
                    $startTime = new DateTime($reservation->startTime ?? '00:00:00');
                    $startTimeFormatted = $startTime->format('h:i A');
                    $endTime = new DateTime($reservation->endTime ?? '00:00:00');
                    $endTimeFormatted = $endTime->format('h:i A'); 
                @endphp
                <div class="reservation-item" data-id="{{ $reservation->id }}"
                    data-name="{{ $reservation->customer->name }}" 
                    data-paidAmount="{{ $reservation->paidAmount }}"
                    data-total="{{ $reservation->grandTotal }}" 
                    data-balance="{{ $reservation->balance }}"
                    data-date="{{ $reservation->date }}" 
                    data-start="{{ $startTimeFormatted }}"
                    data-end="{{ $endTimeFormatted }}" 
                    data-status="{{ $reservation->status }}"
                    style="border-left: 5px solid {{ $statusColor }};">
                    <div class="reservation-id">#{{ $reservation->id }}</div>
                    <div class="reservation-date">{{ $reservation->date }}</div>
                    <div class="reservation-time">{{ $startTimeFormatted }} - {{ $endTimeFormatted }}</div>
                    <div class="reservation-name">{{ $reservation->customer->name }}</div>
                    <div class="reservation-status current">Current</div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-4">
                    No Current reservations found.
                </div>
            @endforelse
            </div>
    
            <!-- Pending Reservations -->
            <div class="reservation-list" id="pending-reservations">
                @forelse ($pendingReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
                @php
                    $statusColor = $reservation->downPayment ? 'orange' : 'yellow';
                    $startTime = new DateTime($reservation->startTime ?? '00:00:00');
                    $startTimeFormatted = $startTime->format('h:i A');
                    $endTime = new DateTime($reservation->endTime ?? '00:00:00');
                    $endTimeFormatted = $endTime->format('h:i A'); 
                @endphp
                <div class="reservation-item" data-id="{{ $reservation->id }}"
                    data-name="{{ $reservation->customer->name }}" 
                    data-paidAmount="{{ $reservation->paidAmount }}"
                    data-total="{{ $reservation->grandTotal }}" 
                    data-balance="{{ $reservation->balance }}"
                    data-date="{{ $reservation->date }}" 
                    data-start="{{ $startTimeFormatted }}"
                    data-end="{{ $endTimeFormatted }}" 
                    data-status="{{ $reservation->status }}"
                    style="border-left: 5px solid {{ $statusColor }};">
                    <div class="reservation-id">#{{ $reservation->id }}</div>
                    <div class="reservation-date">{{ $reservation->date }}</div>
                    <div class="reservation-time">{{ $startTimeFormatted}} - {{ $endTimeFormatted }}</div>
                    <div class="reservation-name">{{ $reservation->customer->name }}</div>
                    <div class="reservation-status pending">Pending</div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-4">
                    No Pending reservations found.
                </div>
            @endforelse
            </div>
    
            <!-- Completed Reservations -->
            <div class="reservation-list" id="completed-reservations">
            @forelse ($completedReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
                @php
                    $startTime = new DateTime($reservation->startTime ?? '00:00:00');
                    $startTimeFormatted = $startTime->format('h:i A');
                    $endTime = new DateTime($reservation->endTime ?? '00:00:00');
                    $endTimeFormatted = $endTime->format('h:i A'); 
                @endphp
                <div class="reservation-item" data-id="{{ $reservation->id }}"
                    data-name="{{ $reservation->customer->name }}" 
                    data-paidAmount="{{ $reservation->paidAmount }}"
                    data-total="{{ $reservation->grandTotal }}" 
                    data-balance="{{ $reservation->balance }}"
                    data-date="{{ $reservation->date }}" 
                    data-start="{{ $startTimeFormatted }}"
                    data-end="{{ $endTimeFormatted }}" 
                    data-status="{{ $reservation->status }}"
                    style="border-left: 5px solid gray;">
                    <div class="reservation-id">#{{ $reservation->id }}</div>
                    <div class="reservation-date">{{ $reservation->date }}</div>
                    <div class="reservation-time">{{ $startTimeFormatted }} - {{ $endTimeFormatted }}</div>
                    <div class="reservation-name">{{ $reservation->customer->name }}</div>
                    <div class="reservation-status completed">Completed</div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-4">
                    No completed reservations found.
                </div>
            @endforelse
            </div>

             <!-- Cancelled/Invalid Reservations -->
            <div class="reservation-list" id="cancelled-reservations">
            @forelse ($cancelledReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)                
                @php
                    $startTime = new DateTime($reservation->startTime ?? '00:00:00');
                    $startTimeFormatted = $startTime->format('h:i A');
                    $endTime = new DateTime($reservation->endTime ?? '00:00:00');
                    $endTimeFormatted = $endTime->format('h:i A'); 
                @endphp
                <div class="reservation-item" data-id="{{ $reservation->id }}"
                    data-name="{{ $reservation->customer->name }}" 
                    data-paidAmount="{{ $reservation->paidAmount }}"
                    data-total="{{ $reservation->grandTotal }}" 
                    data-balance="{{ $reservation->balance }}"
                    data-date="{{ $reservation->date }}" 
                    data-start="{{ $startTimeFormatted }}"
                    data-end="{{ $endTimeFormatted }}" 
                    data-status="{{ $reservation->status }}"
                    style="border-left: 5px solid red;">
                    <div class="reservation-id">#{{ $reservation->id }}</div>
                    <div class="reservation-date">{{ $reservation->date }}</div>
                    <div class="reservation-time">{{ $startTimeFormatted }} - {{ $endTimeFormatted }}</div>
                    <div class="reservation-name">{{ $reservation->customer->name }}</div>
                    <div class="reservation-status cancelled">Cancelled</div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-4">
                    No Cancelled/Invalid reservations found.
                </div>
            @endforelse
            </div>
        </div>
    </div>

<section class="reservation-details hidden">
    <div class="reservation-container">
        <button class="ellipsis-btn">
            <i class="fas fa-ellipsis-h"></i>
        </button>
        <button class="close-btn">&times;</button>
        <div class="menu">
            <!-- Add any necessary menu items here -->
        </div>
        <div class="ellipsis-menu hidden text-xs">
            <button class="edit-reservation"
            >Edit Reservation</button>
            <hr>
            <button class="pay-reservation">Pay</button>
            <hr>
            <button class="cancel-reservation">Cancel Reservation</button>
        </div>
        <div class="downpayment-content">
            <div class="reservation-summary">
                <div class="placeholder-box"></div> <!-- Placeholder for dynamic data -->
            </div>

            <div class="r-details">
                <p>
                    <strong><span id="name" class="reservation-id"></span></strong>
                    <span id="status" class="reservation-status"></span>
                </p>
                
                <p><span id="startTime"></span> - <span id="endTime"></span></p>
                <ul id="modalAmenities"></ul>
                <div id="invalidMessage" class="bg-red-100 text-red-500 rounded-md text-xs p-2">
                    <p>Down payment is invalid. Please submit it again.</p>
                </div>
                <p><strong><span id="grandTotal"></span></strong></p>
                <p><strong><span id="paidAmount"></span></strong></p>
                <p><strong><span id="balance"></span></strong></p>
            </div>

        </div>
    </div>
</section>

<!-- Edit Reservation Modal -->
<div class="edit hidden">
    <div class="edit-content justify-start text-start">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('customer.updateReservation') }}" method="POST" onsubmit="return validateSelection()">
            @csrf
            <span class="close-modal">&times;</span>
            <h2 class="mb-0 pb-0">EDIT RESERVATION DETAILS</h2>
            <p class="text-center pb-2">7:00 AM - 9:00 PM</p>

            <input type="text" id="res_num" name="res_num" hidden>
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" placeholder="DD/MM/YYYY" required>

            <label for="time">Start Time:</label>
            <input type="time" id="starttime" name="starttime" min="07:00" max="21:00" required>

            <label for="time">End Time:</label>
            <input type="time" id="endtime" name="endtime" min="07:00" max="21:00" required>

            <!-- Cottage Dropdown -->
            <div class="mb-2">
                <label>Cottage:</label>
                <div class="relative">
                    <button type="button" 
                            class="cottage-dropdown-toggle w-full flex justify-between items-center px-2 py-1 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Select Cottages
                    </button>
                    <div id="cottage-dropdown" class="cottage-dropdown hidden absolute z-10 mt-1 w-full bg-white shadow-lg max-h-40 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                        <div id="cottage-checkboxes" class="space-y-2 p-2">
                            <!-- Cottage checkboxes will be appended here via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <label>Table:</label>
                <div class="relative">
                    <button type="button" 
                            class="table-dropdown-toggle w-full flex justify-between items-center px-2 py-1 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Select Tables
                    </button>
                    <div id="table-dropdown" class="table-dropdown hidden absolute z-10 mt-1 w-full bg-white shadow-lg max-h-40 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                        <div id="table-checkboxes" class="space-y-2 p-2">
                            <!-- Table checkboxes will be appended here via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
             <!-- Error Message Container -->
             <div id="error-message" style="color: red; font-size: 14px; display: none; margin-bottom: 10px;">
                Please select at least one Cottage or Table before submitting.
            </div>
            <div class="buttons">
                <button class="cancel">Cancel</button>
                <button class="submit">Submit for Verification</button>
            </div>
        </form>
    </div>
</div>

    <!-- Logout Confirmation Modal -->
    <!-- <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeLogoutModal">&times;</span>
            <div class="modal-header">
                <h3>Are you sure you want to log out?</h3>
            </div>
            <div class="modal-footer">
                <button class="btn secondary-btn" id="cancelLogout">Cancel</button>
                <button class="btn primary-btn" id="confirmLogout">Confirm Logout</button>
            </div>
        </div>
    </div> -->

<script>
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
    function convertTo24HourFormat(time) {
        // Ensure the time string is in the correct format (12-hour with AM/PM)
        const regex = /(\d{1,2}):(\d{2})\s([APap][Mm])/;  // Matches "2:30 PM", "10:45 am"
        const match = time.match(regex);
        
        if (match) {
            let hours = parseInt(match[1]);
            const minutes = match[2];
            const period = match[3].toUpperCase(); // AM/PM

            if (period === 'PM' && hours < 12) {
                hours += 12;
            } else if (period === 'AM' && hours === 12) {
                hours = 0;  // Convert 12 AM to 00:00
            }

            return `${hours.toString().padStart(2, '0')}:${minutes}`;
        }

        // If time format doesn't match expected 12-hour with AM/PM
        return 'Invalid Time';
    }
   document.addEventListener("DOMContentLoaded", function () {
    const now = new Date();
    const timezoneOffset = 8 * 60; // Philippines is UTC +8
    const localDate = new Date(now.getTime() + timezoneOffset * 60000);
    const currentDate = localDate.toISOString().split('T')[0]; // Current date in YYYY-MM-DD format

    // Set the minimum date to today for the date input field, but do not set it as the value
    const dateInput = document.getElementById("date");
    dateInput.setAttribute("min", currentDate);
    const reservationItems = document.querySelectorAll(".reservation-item");
    const amenities = @json($allReservations->values()->all());

    reservationItems.forEach(item => {
        item.addEventListener("click", function () {
            const reservationId = this.getAttribute("data-id"); // Changed from item to this
            const reservationName = this.getAttribute("data-name");
            const reservationPaidAmount = this.getAttribute("data-paidAmount");
            const reservationGrandTotal = this.getAttribute("data-total");
            const reservationBalance = this.getAttribute("data-balance");
            const reservationDate = this.getAttribute("data-date");
            const reservationStart = this.getAttribute("data-start");
            const reservationEnd = this.getAttribute("data-end");
            const reservationStatus = this.getAttribute("data-status");
            const statusElement = document.getElementById("status");

            console.log("Reservation ID:", reservationId);

            // Populate modal fields
            document.querySelector(".reservation-id").textContent = `#${reservationId}`;
            document.getElementById("name").textContent = reservationName || '';
            document.getElementById("date").textContent = reservationDate || '';
            document.getElementById("grandTotal").textContent = `Total: ₱${reservationGrandTotal || 0}`;
            document.getElementById("paidAmount").textContent = `Paid Amount: ₱${reservationPaidAmount || 0}`;
            document.getElementById("balance").textContent = `Balance: ₱${reservationBalance || 0}`;
            document.getElementById("startTime").textContent = reservationStart || '';
            document.getElementById("endTime").textContent = reservationEnd || '';

            // Set status
            if (statusElement) {
                const capitalizedStatus = reservationStatus.charAt(0).toUpperCase() + reservationStatus.slice(1);
                statusElement.textContent = capitalizedStatus;
                statusElement.className = "reservation-status";
                
                switch (reservationStatus) {
                    case "pending":
                        statusElement.style.color = "orange";
                        break;
                    case "verified":
                        statusElement.style.color = "green";
                        break;
                    case "invalid":
                    case "cancelled":
                        statusElement.style.color = "red";
                        break;
                    case "completed":
                        statusElement.style.color = "gray";
                        break;
                    default:
                        statusElement.style.color = "black";
                }
            }

            // Fetch and display amenities
            const selectedReservation = amenities.find(r => r.id == reservationId);
            if (selectedReservation) {
                let amenitiesHtml = '';
                selectedReservation.reserved_amenities.forEach(amenity => {
                    const amenityName = amenity.amenity.name;
                    const amenityPrice = amenity.amenity.price;
                    amenitiesHtml += `<li>${amenityName} - ₱${parseFloat(amenityPrice).toFixed(2)}</li>`;
                });
                document.getElementById("modalAmenities").innerHTML = amenitiesHtml;
            }

            // Show the modal
            document.querySelector(".reservation-details").classList.remove("hidden");

            // Update Pay button
            const payBtn = document.querySelector(".pay-reservation");
            payBtn.onclick = function() {
                window.location.href = `/customer/payment/${reservationId}`;
            };

            // Update action buttons
            const editBtn = document.querySelector(".edit-reservation");
            const cancelBtn = document.querySelector(".cancel-reservation");
            const ellipsisBtn = document.querySelector(".ellipsis-btn");
            const invalidMessage = document.getElementById('invalidMessage');

            // Reset all buttons and message first
            payBtn.classList.add("hidden");
            editBtn.classList.add("hidden");
            cancelBtn.classList.add("hidden");
            ellipsisBtn.classList.add("hidden");
            invalidMessage.classList.add("hidden");

            // Set buttons based on reservation status
            if (selectedReservation) {
                const dpStatus = selectedReservation.down_payment?.status;
                const billStatus = selectedReservation.bill?.status;
                const shouldHideCancel = dpStatus === 'verified' || dpStatus === 'pending';

                if (dpStatus === 'verified' && billStatus === 'unpaid') {
                    payBtn.classList.remove("hidden");
                    ellipsisBtn.classList.remove("hidden");
                }

                if (selectedReservation.status === 'pending') {
                    if (!shouldHideCancel) {
                        cancelBtn.classList.remove("hidden");
                    }

                    if (billStatus !== 'paid') {
                        ellipsisBtn.classList.remove("hidden");
                    }

                    if (dpStatus === 'invalid') {
                        invalidMessage.classList.remove("hidden");
                        payBtn.classList.remove("hidden");
                        editBtn.classList.remove("hidden");
                    } else if (!dpStatus) {
                        payBtn.classList.remove("hidden");
                        editBtn.classList.remove("hidden");
                    } else if (dpStatus === 'pending') {
                        payBtn.classList.remove("hidden");
                    }
                } else if (selectedReservation.status === 'verified') {
                    if (!shouldHideCancel) {
                        cancelBtn.classList.remove("hidden");
                    }

                    payBtn.classList.remove("hidden");

                    if (billStatus !== 'paid') {
                        ellipsisBtn.classList.remove("hidden");
                    }
                }
            }
            // Update edit button to use current reservation ID
            editBtn.onclick = function () {
                // Set values from the existing reservation
                document.getElementById('date').value = reservationDate;
                document.getElementById('starttime').value = convertTo24HourFormat(reservationStart);
                document.getElementById('endtime').value = convertTo24HourFormat(reservationEnd);
                document.getElementById('res_num').value = reservationId;

                // Show the edit modal
                document.querySelector('.edit').classList.remove('hidden');

                // Fetch amenities for edit modal using reservation date + res_num + time
                const fetchEditAmenities = (date, startTime, endTime, resNum) => {
                    fetch(`/customer/reservation-records/edit-amenities?date=${date}&starttime=${startTime}&endtime=${endTime}&res_num=${resNum}`)
                        .then(response => response.json())
                        .then(data => {
                            const cottageContainer = document.getElementById('cottage-checkboxes');
                            const tableContainer = document.getElementById('table-checkboxes');

                            cottageContainer.innerHTML = '';
                            tableContainer.innerHTML = '';

                            data.cottages.forEach(cottage => {
                                const wrapper = document.createElement('div');
                                wrapper.className = 'form-check d-flex align-items-center mb-2';

                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.className = 'form-check-input p-2 me-2';
                                checkbox.name = 'cottages[]';
                                checkbox.value = cottage.id;
                                checkbox.id = `cottage-${cottage.id}`;

                                if (data.selectedCottages.includes(cottage.id)) {
                                    checkbox.checked = true;
                                }

                                const label = document.createElement('label');
                                label.className = 'form-check-label m-0 p-0';
                                label.htmlFor = checkbox.id;
                                label.textContent = `${cottage.name} - ₱${cottage.price.toFixed(2)}`;

                                wrapper.appendChild(checkbox);
                                wrapper.appendChild(label);
                                cottageContainer.appendChild(wrapper);
                            });

                            data.tables.forEach(table => {
                                const wrapper = document.createElement('div');
                                wrapper.className = 'form-check d-flex align-items-center mb-2';

                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.className = 'form-check-input p-2 me-2';
                                checkbox.name = 'tables[]';
                                checkbox.value = table.id;
                                checkbox.id = `table-${table.id}`;

                                if (data.selectedTables.includes(table.id)) {
                                    checkbox.checked = true;
                                }

                                const label = document.createElement('label');
                                label.className = 'form-check-label m-0 p-0';
                                label.htmlFor = checkbox.id;
                                label.textContent = `${table.name} - ₱${table.price.toFixed(2)}`;

                                wrapper.appendChild(checkbox);
                                wrapper.appendChild(label);
                                tableContainer.appendChild(wrapper);
                            });
                        })
                        .catch(error => console.error('Fetch error:', error));
                    };
                // Call the function with initial values
                fetchEditAmenities(reservationDate, reservationStart, reservationEnd, reservationId);

                // Add event listeners to date and time inputs to update amenities dynamically
                document.getElementById('date').addEventListener('change', function () {
                    const newDate = this.value;
                    const newStartTime = document.getElementById('starttime').value;
                    const newEndTime = document.getElementById('endtime').value;
                    fetchEditAmenities(newDate, newStartTime, newEndTime, reservationId);
                });

                document.getElementById('starttime').addEventListener('change', function () {
                    const newStartTime = this.value;
                    const newDate = document.getElementById('date').value;
                    const newEndTime = document.getElementById('endtime').value;
                    fetchEditAmenities(newDate, newStartTime, newEndTime, reservationId);
                });

                document.getElementById('endtime').addEventListener('change', function () {
                    const newEndTime = this.value;
                    const newDate = document.getElementById('date').value;
                    const newStartTime = document.getElementById('starttime').value;
                    fetchEditAmenities(newDate, newStartTime, newEndTime, reservationId);
                });
            };

        });

    const cancelButtons = document.querySelectorAll(".cancel-reservation");
    const cancelModal = document.getElementById("cancelModal");
    const cancelYesBtn = document.getElementById("cancelYes");
    const cancelNoBtn = document.getElementById("cancelNo");

    let selectedReservationId = null;

    cancelButtons.forEach(button => {
        button.addEventListener("click", function () {
            selectedReservationId = document.querySelector(".reservation-id").textContent.replace('#', '');
            cancelModal.classList.remove("hidden");
        });
    });

    cancelYesBtn.addEventListener("click", function () {
        if (!selectedReservationId) return;

        fetch(`/customer/reservation-records/${selectedReservationId}/cancel`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: selectedReservationId })
        })
            .then(response => {
                if (response.ok) {
                    showSuccessToast("Reservation cancelled successfully.");
                setTimeout(() => {
                    location.reload();
                }, 1000);

                } else {
                    alert("Failed to cancel reservation.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred.");
            })
            .finally(() => {
                cancelModal.classList.add("hidden");
            });
    });

    cancelNoBtn.addEventListener("click", function () {
        cancelModal.classList.add("hidden");
    });



        // Close the reservation details modal
        const closeDetails = document.querySelector(".close-btn");
        closeDetails.addEventListener("click", function () {
            document.querySelector(".reservation-details").classList.add("hidden");
        const dropdownMenu = document.querySelector(".ellipsis-menu");
    if (dropdownMenu && !dropdownMenu.classList.contains("hidden")) {
        dropdownMenu.classList.add("hidden");
    }
        });

        // Close the edit modal when the close button is clicked
        const closeModal = document.querySelector(".close-modal");
        closeModal.addEventListener("click", function () {
            document.querySelector(".edit").classList.add("hidden");
        });

        // Close the modal if clicked outside
        window.addEventListener("click", function (event) {
            const editModal = document.querySelector(".edit");
            if (event.target === editModal) {
                editModal.classList.add("hidden");
            }
        });

        function showSuccessToast(message = "Action completed successfully.") {
    const toast = document.getElementById("successToast");
    const msg = document.getElementById("successMessage");
    msg.textContent = message;
    toast.classList.remove("hidden");

    // Hide after 3 seconds
    setTimeout(() => {
        toast.classList.add("hidden");
    }, 10000);
}

    });
        // Handle dropdown toggle
        document.addEventListener('click', function(event) {
        if (event.target.closest('.ellipsis-btn')) {
            const dropdownMenu = event.target.closest(".reservation-container").querySelector(".ellipsis-menu");
            dropdownMenu.classList.toggle("hidden");
            event.stopPropagation();
        }
    });
});
// Toggle dropdowns
document.querySelectorAll('.cottage-dropdown-toggle, .table-dropdown-toggle').forEach(button => {
    button.addEventListener('click', function() {
        const dropdownId = this.classList.contains('cottage-dropdown-toggle') 
            ? 'cottage-dropdown' 
            : 'table-dropdown';
        const dropdown = document.getElementById(dropdownId);
        
        // Close all other dropdowns first
        document.querySelectorAll('.cottage-dropdown, .table-dropdown').forEach(d => {
            if (d.id !== dropdownId) d.classList.add('hidden');
        });
        
        // Toggle current dropdown
        dropdown.classList.toggle('hidden');
    });
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.relative')) {
        document.querySelectorAll('.cottage-dropdown, .table-dropdown').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Update button text when checkboxes are checked
function updateButtonText(button, checkboxes) {
    const checkedItems = Array.from(checkboxes)
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.nextSibling.textContent.trim());
    
    if (checkedItems.length > 0) {
        button.textContent = checkedItems.join(', ');
    } else {
        button.textContent = button.classList.contains('cottage-dropdown-toggle') 
            ? 'Select Cottages' 
            : 'Select Tables';
    }
}

    // added Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.status-tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs and content
            document.querySelectorAll('.status-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.reservation-list').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding content
            const status = this.getAttribute('data-status');
            document.getElementById(`${status}-reservations`).classList.add('active');
        });
    });
    
    // Optional: Add click handler for reservation items
    const reservationItems = document.querySelectorAll('.reservation-item');
    reservationItems.forEach(item => {
        item.addEventListener('click', function() {
            // Handle reservation item click (show details modal, etc.)
            const reservationId = this.getAttribute('data-id');
            console.log('Reservation clicked:', reservationId);
            // You would implement your modal opening logic here
        });
    });
});
document.querySelectorAll('.reservation-item').forEach(item => {
        item.addEventListener('click', () => {
            const editBtn = document.querySelector('.edit-reservation');

            editBtn.dataset.id = item.dataset.id;
            editBtn.dataset.date = item.dataset.date;
            editBtn.dataset.start = item.dataset.start;
            editBtn.dataset.end = item.dataset.end;

            document.querySelector('.reservation-details').classList.remove('hidden');
        });
    });


</script>

<div id="cancelModal" class="fixed inset-0 z-[9999] bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-sm mx-auto">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Cancel Reservation</h2>
        <p class="text-sm text-gray-600 mb-4">Are you sure you want to cancel this reservation? This action cannot be undone.</p>
        <div class="flex justify-end gap-3">
            <button id="cancelNo" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm">No</button>
            <button id="cancelYes" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">Yes, Cancel</button>
        </div>
    </div>
</div>

<!-- Success Toast Modal -->
<div id="successToast" class="fixed bottom-6 right-6 z-[9999] bg-green-500 text-white px-6 py-3 rounded shadow-lg flex items-center gap-3 hidden">
    <i class="fas fa-check-circle text-xl"></i>
    <span id="successMessage" class="text-sm font-medium">Reservation cancelled successfully.</span>
</div>

</body>

</html>