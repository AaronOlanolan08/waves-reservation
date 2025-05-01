@extends('admin.vendor.reservation')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/venhistory.css') }}">
@endsection

@section('reservation-content')
	<a href="{{ route('admin.vendor.reservation_calendar') }}" class="back-link">
		<span class="chevron-left"></span> Back to Calendar
	</a>
	<h2 class="head-title">All Reservations</h2>

	<div class="status-bar">
		<span style="color: blue;">● Fully Paid</span>
		<span style="color: lightgreen;">● Partially Paid</span>
		<span style="color: orange;">● With Downpayment</span>
		<span style="color: yellow;">● No Downpayment</span>
		<span style="color: red;">● Cancelled/Invalid</span>
		<span style="color: rgb(51, 51, 51);">● Past</span>
	</div>

	<div class="reservations-container">
		<!-- Cancelled Column -->
		<div class="reservation-column overflow-y-auto">
			<h4>Cancelled</h4>
			@if ($redReservations->isEmpty())
				<p class="text-center">No cancelled reservations.</p>
			@else
				@foreach ($redReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)

					@php
						$date = new DateTime($reservation->date ?? now());
						$startTime = new DateTime($reservation->startTime ?? '00:00:00');
						$endTime = new DateTime($reservation->endTime ?? '00:00:00');
						$statusColor = match ($reservation->status) {
							'invalid', 'cancelled' => 'red',
							default => 'black'
						};
					@endphp
					<div class="reservation-item" data-id="{{ $reservation->id }}" data-name="{{ $reservation->customer->name }}"
						data-paidAmount="{{ $reservation->paidAmount }}" data-total="{{ $reservation->grandTotal }}"
						data-balance="{{ $reservation->balance }}" data-date="{{ $reservation->date }}"
						data-start="{{ $reservation->startTime }}" data-end="{{ $reservation->endTime }}"
						data-status="{{ $reservation->status }}"
						style="border-left: 5px solid {{ $statusColor }}; cursor: pointer;">
						<strong>{{ $reservation->customer->name ?? 'Unknown' }}</strong><br>
						@php

							$date = new DateTime($reservation->date);
							$startTime = new DateTime($reservation->startTime);
							$endTime = new DateTime($reservation->endTime);
						@endphp
						{{ $date->format('Y-m-d') }} | {{ $startTime->format('g:i A') }} -
						{{ $endTime->format('g:i A') }}
					</div>
				@endforeach
			@endif
		</div>

		<!-- Invalid Column -->
		<div class="reservation-column overflow-y-auto">
			<h4>Pending</h4>
			@if ($pendingReservations->isEmpty())
				<p class="text-center">No invalid reservations.</p>
			@else
				@foreach ($pendingReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
					@php
						$date = new DateTime($reservation->date ?? now());
						$startTime = new DateTime($reservation->startTime ?? '00:00:00');
						$endTime = new DateTime($reservation->endTime ?? '00:00:00');
						$statusColor = 'black';

                        // Check for down payment
                        $statusColor = $reservation->downPayment ? 'orange' : 'yellow';
					@endphp
					<div class="reservation-item" data-id="{{ $reservation->id }}" data-name="{{ $reservation->customer->name }}"
						data-paidAmount="{{ $reservation->paidAmount }}" data-total="{{ $reservation->grandTotal }}"
						data-balance="{{ $reservation->balance }}" data-date="{{ $reservation->date }}"
						data-start="{{ $reservation->startTime }}" data-end="{{ $reservation->endTime }}"
						data-status="{{ $reservation->status }}"
						style="border-left: 5px solid {{ $statusColor }}; cursor: pointer;">
						<strong>{{ $reservation->customer->name ?? 'Unknown' }}</strong><br>
						@php

							$date = new DateTime($reservation->date);
							$startTime = new DateTime($reservation->startTime);
							$endTime = new DateTime($reservation->endTime);
						@endphp
						{{ $date->format('Y-m-d') }} | {{ $startTime->format('g:i A') }} -
						{{ $endTime->format('g:i A') }}
					</div>
				@endforeach
			@endif
		</div>

		<!-- Current Column -->
		<div class="reservation-column overflow-y-auto">
			<h4>Current</h4>

			@if ($verifiedReservations->isEmpty())
				<p class="text-center">No current reservations.</p>
			@else
				@foreach ($verifiedReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
					@php
						$date = new DateTime($reservation->date ?? now());
						$startTime = new DateTime($reservation->startTime ?? '00:00:00');
						$endTime = new DateTime($reservation->endTime ?? '00:00:00');
						$statusColor = match ($reservation->bill->status) {
							'paid' => 'blue',
							'partially paid' => 'green',
							default => 'black'
						};
					@endphp
					<div class="reservation-item" data-id="{{ $reservation->id }}" data-name="{{ $reservation->customer->name }}"
						data-paidAmount="{{ $reservation->paidAmount }}" data-total="{{ $reservation->grandTotal }}"
						data-balance="{{ $reservation->balance }}" data-date="{{ $reservation->date }}"
						data-start="{{ $reservation->startTime }}" data-end="{{ $reservation->endTime }}"
						data-status="{{ $reservation->status }}"
						style="border-left: 5px solid {{ $statusColor }}; cursor: pointer;">
						<strong>{{ $reservation->customer->name ?? 'Unknown Customer' }}</strong><br>
						{{ $date->format('Y-m-d') }} |
						{{ $startTime->format('g:i A') }} -
						{{ $endTime->format('g:i A') }}
					</div>
				@endforeach
			@endif
		</div>
		<!-- Completed Column -->
		<div class="reservation-column overflow-y-auto">
			<h4>Completed</h4>
			@if ($completedReservations->isEmpty())
				<p class="text-center">No completed reservations.</p>
			@else
				@foreach ($completedReservations->sortBy(fn($reservation) => new DateTime($reservation->date . ' ' . $reservation->startTime)) as $reservation)
					@php
						$date = new DateTime($reservation->date ?? now());
						$startTime = new DateTime($reservation->startTime ?? '00:00:00');
						$endTime = new DateTime($reservation->endTime ?? '00:00:00');
						$statusColor = match ($reservation->status) {

							'completed' => 'gray',
							default => 'black'
						};
					@endphp
					<div class="reservation-item" data-id="{{ $reservation->id }}" data-name="{{ $reservation->customer->name }}"
						data-paidAmount="{{ $reservation->paidAmount }}" data-total="{{ $reservation->grandTotal }}"
						data-balance="{{ $reservation->balance }}" data-date="{{ $reservation->date }}"
						data-start="{{ $reservation->startTime }}" data-end="{{ $reservation->endTime }}"
						data-status="{{ $reservation->status }}"
						style="border-left: 5px solid {{ $statusColor }}; cursor: pointer;">
						<strong>{{ $reservation->customer->name ?? 'Unknown' }}</strong><br>
						@php
							$date = new DateTime($reservation->date);
							$startTime = new DateTime($reservation->startTime);
							$endTime = new DateTime($reservation->endTime);
						@endphp
						{{ $date->format('Y-m-d') }} | {{ $startTime->format('g:i A') }} -
						{{ $endTime->format('g:i A') }}
					</div>
				@endforeach
			@endif
		</div>
	</div>
	<section class="reservation-details hidden">
		<div class="reservation-container">
			<button class="ellipsis-btn">
				<i class="fas fa-ellipsis-h"></i>
			</button>
			<button class="close-btn">&times;</button>
			<div class="menu">
			</div>
			<div class="downpayment-content">
				<div class="reservation-summary">
					<div class="placeholder-box"></div>
				</div>
				<div class="r-details ms-0 ps-0">
					<p>
						<strong><span class="reservation-name" id="name"></span></strong>
						<span id="status" class="reservation-status"></span>
					</p>

					<p><span id="date"></span>
					</p>
					<p><span id="startTime"></span> - <span id="endTime"></span></p>
					<ul id="modalAmenities"></ul>
					<hr>
					<p><strong id="grandTotal">Total: </strong>
					</p>
					<p><strong id="paidAmount">paid Amount:</strong></p>
					<p><strong id="balance">Balance: </strong></p>
				</div>
			</div>
		</div>
	</section>

@endsection

@section('scripts')
	<script>
		document.addEventListener("DOMContentLoaded", function () {
			const reservationItems = document.querySelectorAll(".reservation-item");
			const amenities = @json($reservations->values()->all());

			reservationItems.forEach(item => {
				item.addEventListener("click", function () {
					const reservationId = item.getAttribute("data-id");
					const reservationName = item.getAttribute("data-name");
					const reservationpaidAmount = item.getAttribute("data-paidAmount");
					const reservationgrandTotal = item.getAttribute("data-total");
					const reservationbalance = item.getAttribute("data-balance");
					const reservationDate = item.getAttribute("data-date");
					const reservationStart = item.getAttribute("data-start");
					const reservationEnd = item.getAttribute("data-end");

					const reservationStatus = item.getAttribute("data-status");
					const statusElement = document.getElementById("status");

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

					// Format times to 24-hour format
					const formatTo12Hour = (time) => {
						const [hours, minutes] = time.split(':');
						const period = hours >= 12 ? 'PM' : 'AM';
						const formattedHours = hours % 12 || 12; // Convert 0 to 12 for 12-hour format
						return `${formattedHours}:${minutes.padStart(2, '0')} ${period}`;
					};

					const formattedStartTime = reservationStart ? formatTo12Hour(reservationStart) : '';
					const formattedEndTime = reservationEnd ? formatTo12Hour(reservationEnd) : '';

					document.getElementById("name").textContent = reservationName || '';
					document.getElementById("date").textContent = reservationDate || '';
					document.getElementById("grandTotal").textContent = `Total: ₱${reservationgrandTotal || 0}`;
					document.getElementById("paidAmount").textContent = `Paid Amount: ₱${reservationpaidAmount || 0}`;
					document.getElementById("balance").textContent = `Balance: ₱${reservationbalance || 0}`;
					document.getElementById("startTime").textContent = formattedStartTime;
					document.getElementById("endTime").textContent = formattedEndTime;
					console.log(reservationpaidAmount, reservationgrandTotal, reservationbalance);

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

					document.querySelector(".reservation-details").classList.remove("hidden");
				});
			});

			// Close button handler
			const closeDetails = document.querySelector(".close-btn");
			closeDetails.addEventListener("click", function () {
				document.querySelector(".reservation-details").classList.add("hidden");
			});
		});
	</script>
@endsection