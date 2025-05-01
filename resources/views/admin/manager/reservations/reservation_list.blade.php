<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="{{ asset('css/res_list.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet"> <!-- For Calendar CSS -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script> <!-- For Calendar JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> <!-- For handling Alert -->
    <script src="https://cdn.tailwindcss.com"></script>
  <title>Reservation</title>
</head>
<body>
   <!-- NAVIGATION BAR SECTION -->

  <nav class="navbar">
	@php
		$user = \App\Models\Admin::find(Auth::id());

		$calendar_route = $user && $user->role === 'Manager'
			? route('admin.reservation.list')
			: route('admin.vendor.reservation_calendar');

		$amenities_route = $user && $user->role === 'Manager'
			? route('admin.manager.amenities', ['type' => 'cottage'])
			: route('admin.vendor.amenities', ['type' => 'cottage']);
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
      <a href="{{ route('admin.manager.profile') }}">
        <button class="profile">
          <i class="material-icons" style="font-size:45px; color: white">
            account_circle
          </i>
        </button>
      </a>
    </div>

  </nav>

  <!-- MAIN SECTION -->

  <main class="main">

    <div class="container mx-auto">
      <div class="flex justify-center mt-3">
          <div class="flex space-x-4">
            <span class="text-xs text-green-500">● Verified</span>
            <span class="text-xs text-yellow-500">● Pending</span>
            <span class="text-xs text-red-500">● Cancelled</span>
            <span class="text-xs text-gray-500">● Completed</span>
          </div>
      </div>
    </div>
  
    <div class="flex justify-center">
      <div id="calendar"></div>
    </div>
  
    <!-- Reservation Details Modal -->
    <div id="reservationModalBackdrop" data-dialog-backdrop="reservationModal" data-dialog-backdrop-close="true" class="text-sm pointer-events-none fixed inset-0 z-[999] grid h-screen w-screen place-items-center bg-black bg-opacity-60 opacity-0 backdrop-blur-sm transition-opacity duration-300">
      <div id="reservationModal" data-dialog="reservationModal" class="relative m-4 p-6 w-2/5 min-w-[25%] max-w-[25%] rounded-lg bg-white shadow-sm">
          <div class="flex justify-between items-center pb-2 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800"><span id="modalCustomerName"></span></h2>
            <span id="modalStatus" class="text-xs font-bold px-2 py-1 rounded-md"></span>
          </div>
  
          <div class="py-2 text-gray-600">
            <p>Date: <span id="modalDate"> </span></p>
            <p>Start Time: <span id="modalStartTime"></span></p>
            <p>End Time: <span id="modalEndTime"></span></p>
  
            <h3 class="font-semibold mt-3">Amenities Reserved:</h3>
            <ul id="modalAmenities" class="list-disc pl-5"></ul>
  
            <hr class="my-3 border-gray-300">
            <p class="font-semibold text-base">Total: <span id="modalTotal"></span> PHP</p>
            <p class="text-sm ">Downpayment (50%): <span id="modalDownpayment"></span> PHP</p>
          </div>
  
          <div class="flex justify-end space-x-2 pt-2 border-t border-gray-200">
            <button id="closeReservationModal" class="rounded-md border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100">Close</button>
          </div>
      </div>
    </div>
  
    <!-- Verify Downpayment Modal -->
    <div 
    id="verifyModalBackdrop"
    data-dialog-backdrop="verifyModal" 
    data-dialog-backdrop-close="true"
    x-data="paymentFormHandler()"
    class="text-sm pointer-events-none fixed inset-0 z-[1000] grid h-screen w-screen place-items-center bg-black bg-opacity-60 opacity-0 backdrop-blur-sm transition-opacity duration-300">
    <div x-ref="formComponent" class="hidden"></div>
    <div 
      id="verifyModal"
      data-dialog="verifyModal" 
      class="relative m-4 p-6 w-2/5 min-w-[35%] max-w-[35%] rounded-lg bg-white shadow-sm">
          <!-- Modal Header -->
          <button id="closeVerifyModal" @click="resetState()" class="absolute top-0 right-2 text-gray-600 text-2xl">&times;</button>
          <div class="flex justify-between items-center pb-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Verify Downpayment</h2>
            <div>
              <span id="dpStatus" class="text-sm font-bold px-2 py-1 rounded-md"></span>
            </div>
  
          </div>
          
          <!-- Modal Content -->
          <div class="py-4 text-gray-600">
            <form id="verifyDownpaymentForm" 
            action="{{ route('admin.vendor.process-payment') }}" 
            data-default-action="{{ route('admin.vendor.process-payment') }}" 
            method="POST">
              @csrf
              <input type="hidden" id="reservationId" name="reservation_id">
              <input type="hidden" id="billId" name="bill_id">
              <input type="hidden" id="dp_id" name="dp_id">
              <input type="hidden" id="status" name="status" value="verified">
            
              <!-- Customer Details -->
              <div class="mb-4">
                  <h2 class="text-xl font-semibold text-gray-800"><span id="verifyCustomerName"></span></h2>
                  <p><span id="modalPhoneNumber"></span></p>
              </div>
            
              <!-- Bill Details -->
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 ps-10 mx-15 pe-10 mb-4">
                  <h6 class="text-sm font-semibold text-gray-700 mb-2">Payment Summary:</h6>
                  <ul id="verifyAmenities" class="list-disc"></ul>
                  <hr class="my-3">
                  <div class="text-sm flex justify-between">
                    <strong>Total:</strong>
                    <span class="font-bold"><span id="verifyTotal"></span></span>
                  </div>
                  <div class="text-sm flex justify-between" id="downPaymentContainer">
                    <strong>Down Payment (50%):</strong>
                    <span class="font-bold"><span id="verifyDownpayment"></span></span>
                  </div>
                  <div class="text-sm flex justify-between" id="paidContainer">
                    <strong>Paid Amount:</strong>
                    <span class="font-bold"><span id="paidAmount"></span></span>
                  </div>
                  <div class="text-sm flex justify-between" id="balanceContainer">
                    <strong>Balance:</strong>
                    <span class="font-bold"><span id="balanceAmount"></span></span>
                  </div>
              </div>
  
              <div class="my-2">
                  <p id="pendingMessage" class="bg-yellow-100 text-yellow-500 rounded-md text-sm p-2 hidden">Please verify the payment.</p>
                  <p id="invalidMessage" class="bg-red-100 text-red-500 rounded-md text-sm p-2 hidden">Contact the customer to send clear photo for proof of payment.</p>
              </div>
  
              <!-- Image -->
              <div class="mb-4">
                  <img id="verifyImage" alt="Downpayment Image" class="rounded-md hidden">
                  <p id="noImageMessage" class="bg-green-400 rounded-md text-sm text-white p-2 hidden">Paid by cash</p>
              </div>     
            
              <!-- Payment Amount Input -->
              <div id="inputPayment">
                  <div class="mb-4">
                    <p class="block text-m font-medium">Reference Number: <span id="verifyReferenceNumber"></span></p>
                  </div>
                  <label for="payment_amount" class="block text-m font-medium">Payment Amount</label>
                  <input 
                  type="number" 
                  id="payment_amount" 
                  placeholder="Enter Paid Amount" 
                  name="payment_amount" 
                  required 
                  class="w-full p-2 border rounded-md"
                  :class="{ 'border-red-500 border-2': showError }"
                  @input="showError = false; hasError = false"
                  >
                  <div class="my-2 bg-red-500 rounded-md ">
                    <p x-show="hasError" class="p-1 text-sm text-white">
                    Please enter a valid payment amount.</p>
                  </div>
              </div>
              <!-- Modal Footer -->
                  <div class="flex justify-end space-x-2 text-sm border-t border-gray-200">
                    <div x-data="{ showInvalidConfirm: false }">
                      <button type="button" id="submitInvalidBtn" 
                      @click="showInvalidConfirm = true" 
                      data-action="{{ route('admin.vendor.invalid-payment') }}" 
                      class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Invalid</button>
                    <!-- Invalid Confirmation Dialog -->
                      <div 
                          x-show="showInvalidConfirm"
                          x-transition:enter="ease-out duration-300"
                          x-transition:enter-start="opacity-0"
                          x-transition:enter-end="opacity-100"
                          x-transition:leave="ease-in duration-200"
                          x-transition:leave-start="opacity-100"
                          x-transition:leave-end="opacity-0"
                          class="fixed inset-0 z-[1100] flex items-center justify-center bg-black bg-opacity-50"
                      >
                          <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                            <h3 class="text-lg font-bold text-gray-900">Mark Payment as Invalid?</h3>
                            <p class="mt-2 text-gray-600">
                              Are you sure the downpayment proof is unclear or invalid?
                            </p>
                            <div class="mt-6 flex justify-end space-x-3">
                              <button
                                  type="button"
                                  @click="showInvalidConfirm = false"
                                  class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                              >Cancel</button>
                              <button
                                  type="button"
                                  @click="
                                    document.getElementById('payment_amount').removeAttribute('required');
                                    document.getElementById('status').value = 'invalid';
                                    document.getElementById('verifyDownpaymentForm').action = document.getElementById('submitInvalidBtn').dataset.action;
                                    document.getElementById('verifyDownpaymentForm').submit();
                                    showInvalidConfirm = false;
                                  "
                                  class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700"
                              >Confirm</button>
                            </div>
                          </div>
                      </div>
                    </div>
                    <!-- Submit Button -->
                    <button 
                      type="button" 
                      id="submitVerifyBtn"
                      @click="trySubmit()"
                      class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"> Submit </button>
                  
                    <!-- Confirmation Dialog -->
                    <div 
                      x-show="showConfirmation"
                      x-transition:enter="ease-out duration-300"
                      x-transition:enter-start="opacity-0"
                      x-transition:enter-end="opacity-100"
                      x-transition:leave="ease-in duration-200"
                      x-transition:leave-start="opacity-100"
                      x-transition:leave-end="opacity-0"
                      class="fixed inset-0 z-[1100] flex items-center text-sm justify-center bg-black bg-opacity-50"
                    >
                      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                          <h3 class="text-lg font-bold text-gray-900">Payment Verified?</h3>
                          <p class="mt-2 text-gray-600">
                            Are you sure the payment was verified? Once verified, it will be confirmed and processed.
                          </p>
                          <div class="mt-6 flex justify-end space-x-3">
                            <button 
                              @click="cancelSubmit"
                              type="button"
                              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                            >
                              Cancel
                            </button>
                            <button 
                              @click="confirmSubmit"
                              type="button" 
                              class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700"
                            >
                              Confirm
                            </button>
                          </div>
                      </div>
                    </div>
                  </div>
              </div>
            </form>
          </div>
      </div>
    </div>
    <!-- Fullscreen Image Modal with Zoom -->
    <div id="fullscreenImageModal" class="hidden">
      <div id="fullscreenImageContainer">
          <span id="closeFullscreen">&times;</span>
          <img id="fullscreenImage" src="" alt="Fullscreen Downpayment Image">
          <div id="zoomControls" class="hidden">
            <button class="zoomButton" id="zoomIn">+</button>
            <button class="zoomButton" id="zoomOut">-</button>
            <button class="zoomButton" id="resetZoom">Reset</button>
          </div>
      </div>
    </div>
  </main>
  <script src="../js/navbutton.js"> </script>
  <script>
		function paymentFormHandler() {
			return {
				showConfirmation: false,
				shouldSubmit: false,
				hasError: false,
				showError: false,

				resetState() {
						this.hasError = false;
						this.showError = false;
						const input = document.getElementById('payment_amount');
						input.classList.remove('border-red-500', 'border-2');
				},

				validateForm() {
						const input = document.getElementById('payment_amount');
						const amount = parseFloat(input.value);

						if (!amount || amount <= 0) {
							input.classList.add('border-red-500', 'border-2');
							input.focus();
							this.hasError = true;
							this.showError = true;
							return false;
						}

						input.classList.remove('border-red-500', 'border-2');
						this.hasError = false;
						this.showError = false;
						return true;
				},

				trySubmit() {
						this.resetState(); // Clear errors before validating
						if (this.validateForm()) {
							this.showConfirmation = true;
						}
				},

				confirmSubmit() {
						this.shouldSubmit = true;
						this.showConfirmation = false;
						this.$nextTick(() => {
							document.getElementById('verifyDownpaymentForm').submit();
						});
				},

				cancelSubmit() {
						this.showConfirmation = false;
						this.shouldSubmit = false;

						// Recheck if still invalid to re-show error
						const input = document.getElementById('payment_amount');
						const amount = parseFloat(input.value);
						if (!amount || amount <= 0) {
							this.hasError = true;
							this.showError = true;
							input.classList.add('border-red-500', 'border-2');
						}
				}
			}
		}
		document.addEventListener('DOMContentLoaded', function() {
			// Initialize FullCalendar with proper headers
			var calendarEl = document.getElementById('calendar');
			var calendar = new FullCalendar.Calendar(calendarEl, {
				initialView: 'dayGridMonth',
				events: '/api/events',
				headerToolbar: {
						start: 'title',
						center: 'reservationsButton monthSelect yearSelect',
						end: '',
				},
				customButtons: {
						reservationsButton: {
						text: 'Reservations',
						click: function () {
							window.location.href = "{{ route('admin.all.reservations') }}";
						}
						},
						walkInRequestButton: {
						text: 'Walk-in Requests',
						click: function () {
							window.location.href = "{{ route('admin.vendor.walk_in') }}";
						}
						},
						editRequestButton: {
						text: 'Edit Requests',
						click: function () {
							alert('Edit Requests button clicked!');
						}
						},
						remainingBalanceButton: {
						text: 'Remaining Balances',
						click: function () {
							window.location.href = "{{ route('admin.vendor.remainingbal') }}";
						}
						},
						monthSelect: {
						click: function () {
							// optional: use this to open your dropdown or bind real dropdown externally
						}
						},

						yearSelect: {
						click: function () {
							// optional
						}
						}
				},
				datesSet: () => {
					applyDropdowns();
					styleCustomButtons();
				},
				eventContent: function(info) {
					let [name, time] = info.event.title.split('|').map(item => item.trim());

					return {
						html: `
							 <div class="flex justify-between items-center w-full 
							 	hover:bg-blue-100 
								text-black hover:text-blue-700 
								px-2 py-1 rounded-lg shadow-sm hover:shadow-md transition">
								<span>${name || 'No Name'}</span>
								<span class="text-xs">${time || ''}</span>
							</div>
						`
					};
				},		
				eventClick: function(info) {
						const eventProps = info.event.extendedProps || {};
						const amenities = eventProps.amenities || [];

						// Basic information
						document.getElementById('modalCustomerName').textContent = eventProps.customer_name || "Unknown";
						document.getElementById('modalPhoneNumber').textContent = eventProps.phone_number || "N/A";
						document.getElementById('modalDate').textContent = eventProps.date || "N/A";
						document.getElementById('modalStartTime').textContent = eventProps.start_time || "N/A";
						document.getElementById('modalEndTime').textContent = eventProps.end_time || "N/A";

						// Status display
						const statusEl = document.getElementById('modalStatus');
						const billStatus = eventProps.bill_status || "unpaid";

						statusEl.textContent = billStatus.charAt(0).toUpperCase() + billStatus.slice(1);
						statusEl.className = `text-sm font-bold px-2 py-1 rounded-md ${
							billStatus === "partially paid" ? "bg-yellow-100 text-yellow-700" :
							billStatus === "paid" ? "bg-green-100 text-green-700" :
							billStatus === "unpaid" ? "bg-red-100 text-red-700" :
							"bg-red-100 text-red-700"
						}`;

						// Amenities handling
						const amenitiesList = document.getElementById('modalAmenities');
						amenitiesList.innerHTML = "";

						if (Array.isArray(amenities) && amenities.length > 0) {
							amenities.forEach(amenity => {
								const li = document.createElement('li');
								li.className = "list-none";

								if (typeof amenity === 'object' && amenity.name && amenity.price !== undefined) {
										li.innerHTML = `<span>${amenity.name}</span><span class="font-bold"> - ₱${parseFloat(amenity.price).toFixed(2)}</span>`;
								} else {
										li.innerHTML = `<span>${amenity}</span><span class="font-bold"> - ₱0.00</span>`; // Fallback if data is missing
								}

								amenitiesList.appendChild(li);
							});
						} else {
							amenitiesList.innerHTML = "<li>No amenities reserved</li>";
						}

						// Financial information
						document.getElementById('modalTotal').textContent = eventProps.total || '0';
						document.getElementById('modalDownpayment').textContent = eventProps.downpayment || '0';

						// Open modal
						document.getElementById('reservationModalBackdrop').classList.remove("opacity-0", "pointer-events-none");
				}
			});
			calendar.render();

			function applyDropdowns() {
				const yearSlot = document.querySelector('.fc-yearSelect-button');
				const monthSlot = document.querySelector('.fc-monthSelect-button');
				const today = new Date();
				const currentYear = today.getFullYear();

				// YEAR DROPDOWN
				if (yearSlot && !document.getElementById('customYearDropdown')) {
				const yearDropdownHTML = `
						<div class="relative w-24" id="customYearDropdown">
						<select class="block w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm text-black focus:outline-none focus:ring-2 focus:ring-blue-400 max-h-[150px] overflow-y-auto">
							${Array.from({ length: 101 }, (_, i) => {
							const year = 1950 + i;
							return `<option value="${year}" ${year === currentYear ? 'selected' : ''}>${year}</option>`;
							}).join('')}
						</select>
						</div>
				`;
				yearSlot.innerHTML = yearDropdownHTML;

				yearSlot.querySelector('select').addEventListener('change', function () {
						const newYear = parseInt(this.value);
						const currentMonth = calendar.getDate().getMonth();
						calendar.gotoDate(new Date(newYear, currentMonth, 1));
				});
				}

				// MONTH DROPDOWN
				if (monthSlot && !document.getElementById('customMonthDropdown')) {
				const monthNames = [
						'January', 'February', 'March', 'April', 'May', 'June',
						'July', 'August', 'September', 'October', 'November', 'December'
				];
				const currentMonth = calendar.getDate().getMonth();

				const monthDropdownHTML = `
						<div class="relative w-32" id="customMonthDropdown">
						<select class="block w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm text-black focus:outline-none focus:ring-2 focus:ring-blue-400">
							${monthNames.map((month, index) => {
							return `<option value="${index}" ${index === currentMonth ? 'selected' : ''}>${month}</option>`;
							}).join('')}
						</select>
						</div>
				`;
				monthSlot.innerHTML = monthDropdownHTML;

				monthSlot.querySelector('select').addEventListener('change', function () {
						const newMonth = parseInt(this.value);
						const currentYear = calendar.getDate().getFullYear();
						calendar.gotoDate(new Date(currentYear, newMonth, 1));
				});
				}
			}

			function styleCustomButtons() {
				const buttonMap = {
				'fc-reservationsButton-button': 'bg-[#002044] hover:bg-[#245f92] text-white text-sm font-medium m-0 px-2 py-1 rounded shadow',
				'fc-walkInRequestButton-button': 'bg-[#002044] hover:bg-[#245f92] text-white text-sm font-medium m-0 px-2 py-1 rounded shadow',
				'fc-editRequestButton-button': 'bg-[#002044] hover:bg-[#245f92] text-white text-sm font-medium m-0 px-2 py-1 rounded shadow',
				'fc-remainingBalanceButton-button': 'bg-[#002044] hover:bg-[#245f92] text-white text-sm font-medium m-0 px-2 py-1 rounded shadow',
				'fc-monthSelect-button': 'bg-transparent text-white font-medium m-0 py-1 rounded',
				'fc-yearSelect-button': 'bg-transparent text-white font-medium m-0 py-1 rounded'
				};

				Object.entries(buttonMap).forEach(([selector, tailwindClasses]) => {
				const button = document.querySelector(`.${selector}`);
				if (button) {
						button.className = tailwindClasses;
				}
				});
			}
			
			// Modal close handlers
			document.getElementById('closeReservationModal').addEventListener('click', () => {
				document.getElementById('reservationModalBackdrop').classList.add("opacity-0", "pointer-events-none");
			});

			document.getElementById('closeVerifyModal').addEventListener('click', () => {
				document.getElementById('verifyModalBackdrop').classList.add("opacity-0", "pointer-events-none");
			});

			// Get the image element that opens the fullscreen modal
			const verifyImage = document.getElementById('verifyImage');
			const fullscreenImageModal = document.getElementById('fullscreenImageModal');
			const fullscreenImage = document.getElementById('fullscreenImage');
			const closeFullscreen = document.getElementById('closeFullscreen');
			let scale = 1;

			// Open the fullscreen image modal when the image is clicked
			verifyImage.addEventListener('click', function() {
				// Set the source of the fullscreen image
				fullscreenImage.src = verifyImage.src;

				// Show the modal
				fullscreenImageModal.style.display = 'block';
				document.body.style.overflow = 'hidden';  // Prevent scrolling when modal is open

				// Reset zoom scale on image open
				scale = 1;
				applyTransform();
			});

			// Zoom functionality (without pan/drag)
			document.getElementById('zoomIn').addEventListener('click', function() {
				scale = Math.min(scale + 0.25, 3);
				applyTransform();
			});

			document.getElementById('zoomOut').addEventListener('click', function() {
				scale = Math.max(scale - 0.25, 1);
				applyTransform();
			});

			document.getElementById('resetZoom').addEventListener('click', function() {
				scale = 1;
				applyTransform();
			});

			// Mouse wheel zoom
			fullscreenImage.addEventListener('wheel', function(e) {
				e.preventDefault();
				const delta = -e.deltaY;
				if (delta > 0) {
						scale = Math.min(scale + 0.1, 3);
				} else {
						scale = Math.max(scale - 0.1, 1);
				}
				applyTransform();
			});

			// Disable all drag/pan functionality
			fullscreenImage.addEventListener('mousedown', function(e) {
				e.preventDefault();
			});

			fullscreenImage.addEventListener('touchstart', function(e) {
				e.preventDefault();
			});

			function applyTransform() {
				fullscreenImage.style.transform = `scale(${scale})`;
			}

			// Close fullscreen image modal
			closeFullscreen.addEventListener('click', function() {
				closeFullscreenModal();
			});

			fullscreenImageModal.addEventListener('click', function(e) {
				if (e.target === this) {
						closeFullscreenModal();
				}
			});

			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape') {
						closeFullscreenModal();
				}
			});

			function closeFullscreenModal() {
				fullscreenImageModal.style.display = 'none';
				document.body.style.overflow = '';  // Re-enable scrolling
				scale = 1;
				applyTransform();
			}
			function paymentValidation() {
				return {
						showConfirmation: false,
						shouldSubmit: false,
						hasError: false,
						showError: false,

						resetState() {
							this.hasError = false;
							this.showError = false;
							const input = document.getElementById('payment_amount');
							if (input) {
								input.classList.remove('border-red-500', 'border-2');
								input.value = '';
							}
						},

						validateForm() {
							const input = document.getElementById('payment_amount');
							const amount = parseFloat(input.value);

							if (!amount || amount <= 0) {
								input.classList.add('border-red-500', 'border-2');
								input.focus();
								this.hasError = true;
								this.showError = true;
								return false;
							}

							input.classList.remove('border-red-500', 'border-2');
							this.hasError = false;
							this.showError = false;
							return true;
						},

						trySubmit() {
							this.resetState(); // Clear errors before validating
							if (this.validateForm()) {
								this.showConfirmation = true;
							}
						},

						confirmSubmit() {
							this.shouldSubmit = true;
							this.showConfirmation = false;
							this.$nextTick(() => {
								document.getElementById('verifyDownpaymentForm').submit();
							});
						},

						cancelSubmit() {
							this.showConfirmation = false;
							this.shouldSubmit = false;

							const input = document.getElementById('payment_amount');
							const amount = parseFloat(input.value);
							if (!amount || amount <= 0) {
								this.hasError = true;
								this.showError = true;
								input.classList.add('border-red-500', 'border-2');
							}
						}
				};
			}
		}); 
  </script>


  
</body>
</html>