<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/ven_profile.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <title>Edit Profile</title>
</head>
<body>

 <!-- NAVIGATION BAR SECTION -->

 <nav class="navbar">

  <div class="left-side-nav">
    <a href="{{ url('dashboard') }}">
      <button class="dashboard" id="dashboard">
        <i class="material-icons nav-icons">dashboard</i> Dashboard
      </button>
    </a>
    <a href="{{ url('ameneties') }}">
      <button class="ameneties" id="ameneties">
        <i class="material-icons nav-icons">holiday_village</i> Amenities
      </button>
    </a>
    <a href="{{ url('reservations') }}">
      <button class="reservations" id="reservation">
        <i class="material-icons nav-icons">date_range</i> Reservations
      </button>
    </a>
  </div>

  <div class="right-side-nav">
    <button class="profile">
      <i class="material-icons" style="font-size:45px; color: white">
        account_circle
      </i>
    </button>
  </div>

</nav>

<!-- MAIN SECTION -->

<main class="main">
  <div class="profile-section">
    <div class="profile-card">
      <h2>Your Admin Profile</h2>
      
      
      <div class="profile-header">
      </div>
      
      <div class="profile-content">
          <div class="profile-avatar">
              <i class="fas fa-user-circle"></i>
          </div>
          
          <div class="profile-details">
              <div class="detail-item name">
                <span>Manny Pacquiao</span>
                
              </div>

              <div class="detail-item name">
                <span class="position-text">Frontdesk - Morning Shift </span>
              </div>

              <div class="detail-item">
                <i class="fas fa-envelope"></i>
                <span>mannyp@gmail.com</span>
              </div>

              <div class="detail-item">
                <i class="fas fa-phone"></i>
                <span>09205678433</span>
              </div>
              
            </div>
          </div>
          
          <div class="profile-actions">
            <button class="btn edit-btn">
              <a>
                <a href="{{ url('ven_editprof') }}">

              </a>
              <i class="fas fa-pencil-alt"></i> Edit Profile
            </button>
            <button class="btn delete-btn" id="deleteAccountBtn">
              <i class="fas fa-trash-alt"></i> Delete Account
            </button>
          </div>
        </div>
      </div>
    </main>

     <!-- Delete Account Modal -->
     <div id="deleteModal" class="modal">
      <div class="modal-content">
          <span class="close">&times;</span>
          <div class="modal-header">
              <h3>Why do you want to delete your account?</h3>
              <p>We're sorry to see you go. Please let us know why you're leaving.</p>
          </div>

         

          <textarea class="reason" id="inpReason" placeholder="Please specify your reason.."></textarea>

          <div class="modal-footer">
              <button class="btn secondary-btn" id="cancelDelete">Cancel</button>
              <button class="btn primary-btn" id="confirmDelete">Submit Request</button>
          </div>
      </div>
  </div>

  <!-- Confirmation Modal -->
<div id="confirmationModal" class="confirmation-modal">
  <div class="confirmation-modal-content">
    <span class="confirmation-close">&times;</span>
    <div class="confirmation-modal-header">
      <h3>Are you sure you want to submit this?</h3>
    </div>

    <div class="confirmation-modal-footer">
      <button class="btn primary-btn" id="yesSubmitBtn">Yes</button>
    </div>
  </div>
</div>

  
<script>
  // Get elements
  const deleteBtn = document.getElementById('deleteAccountBtn');
  const deleteModal = document.getElementById('deleteModal');
  const closeModal = document.querySelector('.modal .close');
  const cancelDelete = document.getElementById('cancelDelete');
  const confirmDeleteBtn = document.getElementById('confirmDelete');
  const inpReason = document.getElementById('inpReason');

  const confirmationModal = document.getElementById('confirmationModal');
  const confirmationClose = document.querySelector('.confirmation-close');
  const yesSubmitBtn = document.getElementById('yesSubmitBtn');

  // Open delete modal
  deleteBtn.addEventListener('click', () => {
    deleteModal.style.display = 'block';
  });

  // Close delete modal
  closeModal.addEventListener('click', () => {
    deleteModal.style.display = 'none';
  });

  cancelDelete.addEventListener('click', () => {
    deleteModal.style.display = 'none';
  });

  // Close when clicking outside modals
  window.addEventListener('click', (e) => {
    if (e.target == deleteModal) {
      deleteModal.style.display = 'none';
    }
    if (e.target == confirmationModal) {
      confirmationModal.style.display = 'none';
    }
  });

  // When Submit Request is clicked
  confirmDeleteBtn.addEventListener('click', () => {
    const reason = inpReason.value.trim();
    if (reason.length === 0) {
      alert('Please provide a reason before submitting.');
    } else {
      confirmationModal.style.display = 'block';
    }
  });

  // Close confirmation modal
  confirmationClose.addEventListener('click', () => {
    confirmationModal.style.display = 'none';
  });

  // When 'Yes' button is clicked
  yesSubmitBtn.addEventListener('click', () => {
    alert('Your request has been submitted. Thank you!');
    confirmationModal.style.display = 'none';
    deleteModal.style.display = 'none';
    inpReason.value = ''; // clear textarea
  });
</script>

  
</body>
</html>