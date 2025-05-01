<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile</title>
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

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

        </aside>
        <main class="profile-section">
            <div class="profile-card">
                <div class="profile-header"></div>
                <div class="profile-content">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-details">
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
                    <a href="{{ route('profile.edit', ['id' => $customer->id]) }}" class="btn edit-btn" style="text-decoration: none ;">
                        <i class="fas fa-pencil-alt"></i> Edit Profile
                    </a>
                    <button class="btn delete-btn" id="deleteAccountBtn">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h3>Why do you want to delete your account?</h3>
                <p>We're sorry to see you go. Please let us know why you're leaving.</p>
            </div>

            <select class="reason-select" id="deleteReason">
                <option value="" disabled selected>Select a reason...</option>
                <option value="no-longer-need">I no longer need this account</option>
                <option value="privacy-concerns">I have privacy concerns</option>
                <option value="poor-experience">I had a poor experience</option>
                <option value="found-better-service">I found a better service</option>
                <option value="other">Other reason</option>
            </select>

            <textarea class="other-reason" id="otherReason" placeholder="Please specify your reason..."></textarea>

            <div class="modal-footer">
                <button class="btn secondary-btn" id="cancelDelete">Cancel</button>
                <button class="btn primary-btn" id="confirmDelete">Submit Request</button>
            </div>
        </div>
    </div>

    <!-- Modal Script -->
    <script>
         document.addEventListener('DOMContentLoaded', function() {
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteModal = document.getElementById('deleteModal');
    const cancelDelete = document.getElementById('cancelDelete');
    const confirmDelete = document.getElementById('confirmDelete');
    const deleteReason = document.getElementById('deleteReason');
    const otherReason = document.getElementById('otherReason');
    const closeBtn = document.querySelector('.close');
    
    // Open modal
    deleteAccountBtn.addEventListener('click', function() {
        deleteModal.style.display = 'flex';
    });
    
    // Toggle other reason textarea
    deleteReason.addEventListener('change', function() {
        if (this.value === 'other') {
            otherReason.style.display = 'block';
            otherReason.required = true;
        } else {
            otherReason.style.display = 'none';
            otherReason.required = false;
        }
    });
    
    // Close modal
    function closeModal() {
        deleteModal.style.display = 'none';
        deleteReason.selectedIndex = 0;
        otherReason.value = '';
        otherReason.style.display = 'none';
    }
    
    closeBtn.addEventListener('click', closeModal);
    cancelDelete.addEventListener('click', closeModal);
    
    // Click outside modal to close
    window.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            closeModal();
        }
    });
    
    // Confirm delete
    confirmDelete.addEventListener('click', function() {
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
        
        console.log('Account deletion requested. Reason:', reason);
        alert('Your deletion request has been sent for review.');
        closeModal();
    });
});
    </script>
</body>

</html>