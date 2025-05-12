<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile</title>
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>
    <div class="container">
        <aside class="sidebar">
            <div class="back-button">
                <a href="{{ route('customer.reservation') }}" class="back-link">
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
            <nav class="menu">
                <a href="{{ route('customer.profile') }}" class="active"><i class="fas fa-user"></i> Profile</a>
                <a href="{{ route('customer.reservation.records') }}"><i class="fas fa-calendar-check"></i>
                    Reservations</a>

                <!-- nag add kog button ani for acc balance -->

                <!-- <a href="#"><i class="fas fa-wallet"></i> Account Balance</a> -->

            </nav>

            <!-- logout -->
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>

            <button id="logoutButton" class="logout">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </button>
        </aside>

        <!--- MAIN CONTENT SECTION -->
        <main class="profile-section">
            <div class="profile-card">
                <h2 class="profile-title">Your Profile</h2>

                <div class="profile-content">
                    <div class="profile-avatar">
                        <div class="avatar-circle">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    
                    <div class="profile-details">
                        @if(session('success'))
                            <div style="text-xs color: green;">{{ session('success') }}</div>
                        @endif
                        <div class="detail-item name">
                            <span>{{ $customer->name }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span>{{ $customer->email }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span>{{ $customer->number }}</span>
                        </div>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="{{ route('profile.edit', ['id' => $customer->id]) }}" class="btn edit-btn"
                        style="text-decoration: none;">
                        <i class="fas fa-pencil-alt"></i> Edit Profile
                    </a>

                    @if ($pendingRequest)
                        <button class="btn delete-btn" disabled style="background-color: grey; cursor: not-allowed;">
                            <i class="fas fa-hourglass-half"></i> Deletion Request Pending
                        </button>
                    @else
                        <button class="btn delete-btn" id="deleteAccountBtn">
                            <i class="fas fa-trash-alt"></i> Delete Account
                        </button>
                    @endif
                </div>

            </div>
        </main>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeDeleteModal">&times;</span>
            <div class="modal-header">
                <h3>Why do you want to delete your account?</h3>
                <p>We're sorry to see you go. Please let us know why you're leaving.</p>
            </div>

            <form id="deleteForm" method="POST" action="{{ route('profile.delete', ['id' => $customer->id]) }}">
                @csrf
                @method('PATCH')

                <select class="reason-select" id="deleteReason" name="reason">
                    <option value="" disabled selected>Select a reason...</option>
                    <option value="no-longer-need">I no longer need this account</option>
                    <option value="privacy-concerns">I have privacy concerns</option>
                    <option value="poor-experience">I had a poor experience</option>
                    <option value="found-better-service">I found a better service</option>
                    <option value="other">Other reason</option>
                </select>

                <textarea class="other-reason" id="otherReason" name="other_reason"
                    placeholder="Please specify your reason..."></textarea>

                <div class="modal-footer">
                    <button type="button" class="btn secondary-btn" id="cancelDelete">Cancel</button>
                    <button type="submit" class="btn primary-btn" id="confirmDelete">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal">
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Modal functions
            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                modal.style.display = 'flex';
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                modal.style.display = 'none';
            }

            // Delete Account Modal
            const deleteAccountBtn = document.getElementById('deleteAccountBtn');
            const deleteModal = document.getElementById('deleteModal');
            const cancelDelete = document.getElementById('cancelDelete');
            const confirmDelete = document.getElementById('confirmDelete');
            const closeDeleteBtn = document.getElementById('closeDeleteModal');
            const deleteReason = document.getElementById('deleteReason');
            const otherReason = document.getElementById('otherReason');

            deleteReason.addEventListener('change', function () {
                if (deleteReason.value === 'other') {
                    otherReason.style.display = 'block';
                } else {
                    otherReason.style.display = 'none';
                    otherReason.value = '';
                }
            });


            if (deleteAccountBtn) {
                deleteAccountBtn.addEventListener('click', function () {
                    openModal('deleteModal');
                });
            }

            cancelDelete.addEventListener('click', function () {
                closeModal('deleteModal');
            });

            closeDeleteBtn.addEventListener('click', function () {
                closeModal('deleteModal');
            });

            // Confirm deletion logic (handling form submission or alert)
            confirmDelete.addEventListener('click', function () {
                let reason;
                if (deleteReason.value === 'other') {
                    reason = otherReason.value.trim();
                    if (!reason) {
                        alert('Please specify your reason for deletion');
                        return;
                    }
                } else {
                    reason = deleteReason.value;
                    if (!reason) {
                        alert('Please select a reason for deletion');
                        return;
                    }
                }
                alert('Your deletion request has been sent for review.');
                closeModal('deleteModal');
            });

            // Logout Modal
            const logoutButton = document.getElementById('logoutButton');
            const logoutModal = document.getElementById('logoutModal');
            const cancelLogout = document.getElementById('cancelLogout');
            const confirmLogout = document.getElementById('confirmLogout');
            const closeLogoutBtn = document.getElementById('closeLogoutModal');

            logoutButton.addEventListener('click', function () {
                openModal('logoutModal');
            });

            cancelLogout.addEventListener('click', function () {
                closeModal('logoutModal');
            });

            closeLogoutBtn.addEventListener('click', function () {
                closeModal('logoutModal');
            });

            confirmLogout.addEventListener('click', function () {
                document.getElementById('logoutForm').submit();
                closeModal('logoutModal');
            });

            // Close modal when clicking outside of it
            window.addEventListener('click', function (event) {
                if (event.target === deleteModal) {
                    closeModal('deleteModal');
                } else if (event.target === logoutModal) {
                    closeModal('logoutModal');
                }
            });
        });
    </script>
</body>

</html>