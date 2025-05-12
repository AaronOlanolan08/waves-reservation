<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/amenities.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Cancelled Amenities</title>
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

    <!-- SIDEBAR + MAIN CONTENT SECTION -->
    <div class="container">
        <nav class="sidebar">
            <div class="icon-container" id="cottages-tab">
                <i class="material-icons side-icons">storefront</i>
                <p class="icon-label">Cottages</p>
            </div>
            <div class="icon-container" id="tables-tab">
                <i class="material-icons side-icons">dining</i>
                <p class="icon-label">Tables</p>
            </div>
            <div class="icon-container" id="cancel-tab">
                <i class="material-icons side-icons">block</i>
                <p class="icon-label">Cancelled</p>
            </div>
        </nav>

        <main class="main p-6">
            <h2 class="text-2xl font-bold mb-4">Cancelled Amenities (From Today Onward)</h2>
            <div id="message-box" class="mb-4 text-green-600 font-semibold hidden"></div>
            @if ($cancelledAmenities->isEmpty())
                <div class="text-center text-gray-600 text-lg mt-6">
                    No cancelled amenities available.
                </div>
            @else
                <table class="min-w-full table-fixed border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200 text-left">
                            <th class="w-1/4 border border-gray-300 px-4 py-2">Customer Name</th>
                            <th class="w-1/4 border border-gray-300 px-4 py-2">Amenity</th>
                            <th class="w-1/4 border border-gray-300 px-4 py-2">Date</th>
                            <th class="w-1/4 border border-gray-300 px-4 py-2">Activate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cancelledAmenities as $item)
                            <tr class="align-top">
                                <td class="border border-gray-300 px-4 py-2">{{ $item->reservation->customer->name ?? 'N/A' }}
                                </td>
                                <td class="border border-gray-300 px-4 py-2">{{ $item->amenity->name ?? 'N/A' }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $item->reservation->date }}</td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <button class="bg-green-500 text-white px-4 py-2 rounded activate-btn"
                                        data-id="{{ $item->amenity->id }}">
                                        Activate Amenity
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>    
            @endif
        </main>
    </div>
    <!-- Custom Confirmation Modal -->
    <div id="confirmation-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-sm w-full shadow-lg">
            <h3 class="text-lg font-semibold mb-4">Confirm Activation</h3>
            <p class="mb-6">Are you sure you want to activate this amenity?</p>
            <div class="flex justify-end gap-3">
                <button id="cancel-modal-btn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button id="confirm-modal-btn"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Confirm</button>
            </div>
        </div>
    </div>
    <script>
        // Sidebar navigation
        document.getElementById('cottages-tab').addEventListener('click', () => {
            window.location.href = "{{ route('admin.vendor.amenities', ['type' => 'cottage']) }}";
        });

        document.getElementById('tables-tab').addEventListener('click', () => {
            window.location.href = "{{ route('admin.vendor.amenities', ['type' => 'table']) }}";
        });

        document.getElementById('cancel-tab').addEventListener('click', () => {
            window.location.href = "{{ route('admin.vendor.cancel') }}";
        });

        let selectedAmenityId = null;
        let selectedButton = null;

        const messageBox = document.getElementById('message-box');
        const modal = document.getElementById('confirmation-modal');
        const confirmBtn = document.getElementById('confirm-modal-btn');
        const cancelBtn = document.getElementById('cancel-modal-btn');

        // Attach event to all activate buttons
        document.querySelectorAll('.activate-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedAmenityId = btn.dataset.id;
                selectedButton = btn;
                modal.classList.remove('hidden');
            });
        });

        // Cancel modal
        cancelBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            selectedAmenityId = null;
            selectedButton = null;
        });

        // Confirm activation
        confirmBtn.addEventListener('click', () => {
            if (!selectedAmenityId) return;

            fetch("{{ route('admin.vendor.activate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ amenity_id: selectedAmenityId })
            })
                .then(res => res.json())
                .then(data => {
                    modal.classList.add('hidden');
                    if (data.success) {
                        messageBox.textContent = 'Amenity activated!';
                        messageBox.classList.remove('hidden', 'text-red-600');
                        messageBox.classList.add('text-green-600');

                        selectedButton.disabled = true;
                        selectedButton.textContent = 'Activated';

                        setTimeout(() => {
                            messageBox.classList.add('hidden');
                        }, 3000);
                    } else {
                        messageBox.textContent = 'Activation failed.';
                        messageBox.classList.remove('hidden', 'text-green-600');
                        messageBox.classList.add('text-red-600');
                    }
                })
                .catch(error => {
                    modal.classList.add('hidden');
                    console.error("Request Error:", error);
                    messageBox.textContent = 'An error occurred while activating.';
                    messageBox.classList.remove('hidden', 'text-green-600');
                    messageBox.classList.add('text-red-600');
                    selectedAmenityId = null;
                    selectedButton = null;
                });

        });

    </script>

</body>

</html>