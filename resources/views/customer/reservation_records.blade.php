<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WAVES Beach Resort</title>
    <link rel="stylesheet" href="{{ asset('css/reservation_records.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jaldi&family=Allura&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

    <div class="content">
        <div class="title-container">
        <div class="content-card">
            <div class="content-header">
        <h2>Your Reservations</h2>

        <div class="status-bar">
            <span style="color: blue;">● Fully Paid</span>
            <span style="color: lightgreen;">● Partially Paid</span>
            <span style="color: orange;">● With Downpayment</span>
            <span style="color: yellow;">● No Downpayment</span>
            <span style="color: red;">● Cancelled/Invalid</span>
            <span style="color: rgb(51, 51, 51);">● Past</span>
            </div>
            </div>
            
        <div class="reservations">
            <div class="reservation-column">
                <h4>Cancelled / Invalid</h4>
                @foreach ($redReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
                                @php
                                    $date = new DateTime($reservation->date ?? now());
                                    $startTime = new DateTime($reservation->startTime ?? '00:00:00');
                                    $endTime = new DateTime($reservation->endTime ?? '00:00:00');
                                    $statusColor = match ($reservation->status) {
                                        'pending' => 'orange',
                                        'verified' => 'green',
                                        'invalid', 'cancelled' => 'red',
                                        'completed' => 'gray',
                                        default => 'black',
                                    };
                                @endphp
                                <div class="reservation-item" data-id="{{ $reservation->id }}"
                                    data-name="{{ $reservation->customer->name }}" data-paidAmount="{{ $reservation->paidAmount }}"
                                    data-total="{{ $reservation->grandTotal }}" data-balance="{{ $reservation->balance }}"
                                    data-date="{{ $reservation->date }}" data-start="{{ $reservation->startTime }}"
                                    data-end="{{ $reservation->endTime }}" data-status="{{ $reservation->status }}"
                                    style="border-left: 5px solid {{ $statusColor }}; cursor: pointer;">
                                    <strong>#{{ $reservation->id }}</strong><br>
                                    {{ $reservation->date }} | {{ $reservation->startTime }} - {{ $reservation->endTime }}
                                </div>
                @endforeach
            </div>

            <div class="reservation-column">
                <h4>Pending</h4>
                @foreach ($pendingReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
                                @php
                                    $date = new DateTime($reservation->date ?? now());
                                    $startTime = new DateTime($reservation->startTime ?? '00:00:00');
                                    $endTime = new DateTime($reservation->endTime ?? '00:00:00');
                                    // Default color
                                    $statusColor = 'black';

                                    // Check for down payment
                                    $statusColor = $reservation->downPayment ? 'orange' : 'yellow';
                                @endphp
                                <div class="reservation-item" data-id="{{ $reservation->id }}"
                                    data-name="{{ $reservation->customer->name }}" data-paidAmount="{{ $reservation->paidAmount }}"
                                    data-total="{{ $reservation->grandTotal }}" data-balance="{{ $reservation->balance }}"
                                    data-date="{{ $reservation->date }}" data-start="{{ $reservation->startTime }}"
                                    data-end="{{ $reservation->endTime }}" data-status="{{ $reservation->status }}"
                                    style="border-left: 5px solid {{ $statusColor }}; cursor: pointer;">
                                    <strong>#{{ $reservation->id }}</strong><br>
                                    {{ $reservation->date }} | {{ $reservation->startTime }} - {{ $reservation->endTime }}
                                </div>
                @endforeach
            </div>

            <div class="reservation-column">
                <h4>Current</h4>
                @foreach ($paidReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
                                @php
                                    $date = new DateTime($reservation->date ?? now());
                                    $startTime = new DateTime($reservation->startTime ?? '00:00:00');
                                    $endTime = new DateTime($reservation->endTime ?? '00:00:00');
                                    $statusColor = match ($reservation->bill->status) {
                                        'paid' => 'blue',
                                        'partially paid' => 'green',
                                    };
                                @endphp
                                <div class="reservation-item" data-id="{{ $reservation->id }}"
                                    data-name="{{ $reservation->customer->name }}" data-paidAmount="{{ $reservation->paidAmount }}"
                                    data-total="{{ $reservation->grandTotal }}" data-balance="{{ $reservation->balance }}"
                                    data-date="{{ $reservation->date }}" data-start="{{ $reservation->startTime }}"
                                    data-end="{{ $reservation->endTime }}" data-status="{{ $reservation->status }}"
                                    style="border-left: 5px solid {{ $statusColor }}; cursor: pointer;">
                                    <strong>#{{ $reservation->id }}</strong><br>
                                    {{ $reservation->date }} | {{ $reservation->startTime }} - {{ $reservation->endTime }}
                                </div>
                @endforeach
            </div>

            <div class="reservation-column">
                <h4>Completed</h4>
                @foreach ($completedReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
                                @php
                                    $date = new DateTime($reservation->date ?? now());
                                    $startTime = new DateTime($reservation->startTime ?? '00:00:00');
                                    $endTime = new DateTime($reservation->endTime ?? '00:00:00');
                                    $statusColor = match ($reservation->status) {
                                        'pending' => 'orange',
                                        'verified' => 'green',
                                        'invalid', 'cancelled' => 'red',
                                        'completed' => 'gray',
                                        default => 'black',
                                    };
                                @endphp
                                <div class="reservation-item" data-id="{{ $reservation->id }}"
                                    data-name="{{ $reservation->customer->name }}" data-paidAmount="{{ $reservation->paidAmount }}"
                                    data-total="{{ $reservation->grandTotal }}" data-balance="{{ $reservation->balance }}"
                                    data-date="{{ $reservation->date }}" data-start="{{ $reservation->startTime }}"
                                    data-end="{{ $reservation->endTime }}" data-status="{{ $reservation->status }}"
                                    style="border-left: 5px solid {{ $statusColor }}; cursor: pointer;">
                                    <strong>#{{ $reservation->id }}</strong><br>
                                    {{ $reservation->date }} | {{ $reservation->startTime }} - {{ $reservation->endTime }}
                                </div>
                @endforeach
            </div>

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
        <div class="dropdown-menu hidden text-xs">
            <button class="edit-reservation">Edit Reservation</button>
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
                <hr>
                <p><strong>Total: <span id="grandTotal"></span></strong></p>
                <p><strong>Paid Amount: <span id="paidAmount"></span></strong></p>
                <p><strong>Balance: <span id="balance"></span></strong></p>
            </div>

        </div>
    </div>
</section>

<!-- Edit Reservation Modal -->
<div class="edit hidden">
    <div class="edit-content">
        <span class="close-modal">&times;</span>
        <h2>EDIT RESERVATION DETAILS</h2>

        <label for="date">Date:</label>
        <input type="date" id="date" placeholder="DD/MM/YYYY">

        <label for="time">Start Time:</label>
        <input type="time" id="starttime" placeholder="00:00:00">

        <label for="time">End Time:</label>
        <input type="time" id="endtime" placeholder="00:00:00">

        <label for="cottage">Cottage:</label>
        <select id="cottage">
            <option>Select</option>
        </select>

        <label for="table">Table:</label>
        <select id="table">
            <option>Select</option>
        </select>

        <div class="buttons">
            <button class="cancel">Cancel</button>
            <button class="submit">Submit for Verification</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const reservationItems = document.querySelectorAll(".reservation-item");
        const amenities = @json($allReservations->values()->all());

        reservationItems.forEach(item => {
            item.addEventListener("click", function () {
                var reservationId = item.getAttribute("data-id");
                const reservationName = item.getAttribute("data-name");
                const reservationpaidAmount = item.getAttribute("data-paidAmount");
                const reservationgrandTotal = item.getAttribute("data-total");
                const reservationbalance = item.getAttribute("data-balance");
                const reservationDate = item.getAttribute("data-date");
                const reservationStart = item.getAttribute("data-start");
                const reservationEnd = item.getAttribute("data-end");

                const reservationStatus = item.getAttribute("data-status");
                const statusElement = document.getElementById("status");

                document.querySelector(".reservation-id").textContent = reservationId;

                console.log("Reservation ID:", reservationId);

                if (statusElement) {
                    // Capitalize first letter
                    const capitalizedStatus = reservationStatus.charAt(0).toUpperCase() + reservationStatus.slice(1);
                    statusElement.textContent = capitalizedStatus;

                    // Reset class/style
                    statusElement.className = "reservation-status";

                    // Apply color
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

                document.getElementById("name").textContent = reservationId || '';
                document.getElementById("date").textContent = reservationDate || '';
                document.getElementById("grandTotal").textContent = `Total: ₱${reservationgrandTotal || 0}`;
                document.getElementById("paidAmount").textContent = `Paid Amount: ₱${reservationpaidAmount || 0}`;
                document.getElementById("balance").textContent = `Balance: ₱${reservationbalance || 0}`;
                document.getElementById("startTime").textContent = reservationStart || '';
                document.getElementById("endTime").textContent = reservationEnd || '';
                console.log(reservationpaidAmount, reservationgrandTotal, reservationbalance);

                const selectedReservation = amenities.find(r => r.id == reservationId);

                if (selectedReservation) {
                    let amenitiesHtml = '';

                    // Iterate through reserved amenities and fetch relevant data
                    selectedReservation.reserved_amenities.forEach(amenity => {
                        const amenityName = amenity.amenity.name;
                        const amenityPrice = amenity.amenity.price;
                        amenitiesHtml += `<li>${amenityName} - ₱${parseFloat(amenityPrice).toFixed(2)}</li>`;
                    });

                    document.getElementById("modalAmenities").innerHTML = amenitiesHtml;
                }

                document.querySelector(".reservation-details").classList.remove("hidden");

                // Attach click event to Pay button
                document.querySelector(".pay-reservation").addEventListener("click", function () {
                    // Redirect to downpayment page with reservationId as a URL parameter
                    const url = `/customer/downpayment/${reservationId}`; // Make sure the URL includes the customer prefix
                    window.location.href = url;
                });
                document.querySelector(".ellipsis-btn").classList.add("hidden");

                const editBtn = document.querySelector(".edit-reservation");
                const cancelBtn = document.querySelector(".cancel-reservation");
                const payBtn = document.querySelector(".pay-reservation");

                if (selectedReservation.status === 'pending') {
                    payBtn.classList.remove("hidden");
                    editBtn.classList.remove("hidden");
                    cancelBtn.classList.remove("hidden");

                    if (selectedReservation.status === 'pending' && selectedReservation.down_payment) {
                        editBtn.classList.add("hidden");
                        payBtn.classList.add("hidden");
                        cancelBtn.classList.remove("hidden");
                    }
                    document.querySelector(".ellipsis-btn").classList.remove("hidden");
                }

                if (selectedReservation.status === 'verified') {
                    document.querySelector(".ellipsis-btn").classList.remove("hidden");
                    editBtn.classList.add("hidden");   // Can't edit if verified or has downpayment
                    cancelBtn.classList.remove("hidden");
                    payBtn.classList.add("hidden");
                }


                // Show the modal
                document.querySelector(".reservation-details").classList.remove("hidden");
            });
        });

        // Open the dropdown menu when the 3-dotted icon is clicked
        const ellipsisButtons = document.querySelectorAll(".ellipsis-btn");

        ellipsisButtons.forEach(button => {
            button.addEventListener("click", function (event) {
                const dropdownMenu = event.target.closest(".reservation-container").querySelector(".dropdown-menu");

                // Toggle the visibility of the dropdown menu
                dropdownMenu.classList.toggle("hidden");

                // Prevent event propagation to avoid triggering other click events
                event.stopPropagation();
            });
        });

        // Open the edit modal when the "Edit Reservation" button is clicked
        const editButtons = document.querySelectorAll(".edit-reservation");

        editButtons.forEach(button => {
            button.addEventListener("click", function () {
                // Show the edit modal
                document.querySelector(".edit").classList.remove("hidden");

                // Populate the edit modal with the reservation data (you can add more logic for this if necessary)
                const selectedReservation = {}; // Get the selected reservation data (from the click event or state)

                document.querySelector("#date").value = selectedReservation.date || ''; // Set the date field
                document.querySelector("#time").value = selectedReservation.time || ''; // Set the time field
                // Populate the cottage and table select options dynamically if needed
            });
        });

        // Cancel reservation with confirmation
        // Cancel reservation with confirmation
        const cancelButtons = document.querySelectorAll(".cancel-reservation");

        cancelButtons.forEach(button => {
            button.addEventListener("click", function () {
                const reservationId = document.querySelector(".reservation-id").textContent;

                if (confirm("Are you sure you want to cancel this reservation?")) {
                    fetch(`/customer/reservation-records/${reservationId}/cancel`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ id: this.reservationId })
                    })
                        .then(response => {
                            if (response.ok) {
                                alert("Reservation cancelled successfully.");
                                location.reload();
                            } else {
                                alert("Failed to cancel reservation.");
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            alert("An error occurred.");
                        });
                }
            });
        });



        // Close the reservation details modal
        const closeDetails = document.querySelector(".close-btn");
        closeDetails.addEventListener("click", function () {
            document.querySelector(".reservation-details").classList.add("hidden");
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
    });


</script>
</body>

</html>