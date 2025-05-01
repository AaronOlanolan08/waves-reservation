<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/edit_profile.css') }}">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="back-button">
                <a href="{{ route('customer.reservation') }}" class="back-link">
                    <i class="fas fa-chevron-left"></i>
                    <span>Back to main</span>
                </a>
            </div>

            <div class="customer-profile">
                <i class="fas fa-user-circle profile-icon"></i>
                <div class="customer-name">{{ $customer->name }}</div>
            </div>

            <div class="menu">
                <a href="{{ route('customer.profile') }}" class="active">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <a href="{{ route('customer.reservation.records') }}">
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

        <!-- Main Content -->
        <div class="main-content">
            <div class="profile-section">
                <h2>Edit Profile</h2>

                <!-- Success Message (only shown if session has success) -->
                @if(session('success'))
                    <div class="alert alert-success" style="color: green; font-size: 18px;">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                <!-- Error Messages (e.g., email already taken) -->
                @if($errors->any())
                    <div class="alert alert-error" style="color: red; font-size: 18px;">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="profile-form" action="{{ route('profile.update', ['id' => $customer->id]) }}" method="POST"
                    onsubmit="return confirmEdit()">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', $customer->name) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input"
                            value="{{ old('email', $customer->email) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact No.</label>
                        <input type="tel" name="number" class="form-input"
                            value="{{ old('number', $customer->number) }}">
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-cancel" onclick="cancelEdit()">Cancel</button>
                        <button type="submit" class="btn btn-save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Confirmation before editing
        function confirmEdit() {
            const name = document.querySelector('input[name="name"]').value;
            const email = document.querySelector('input[name="email"]').value;
            const number = document.querySelector('input[name="number"]').value;

            const confirmed = confirm("Are you sure you want to save the changes?");
            return confirmed;
        }

        // Handle Cancel button
        function cancelEdit() {
            if (confirm("Are you sure you want to cancel the changes?")) {
                window.location.href = "{{ route('customer.profile') }}"; // Redirect to the profile page
            }
        }
    </script>
</body>

</html>