<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WAVES Beach Resort</title>
    <link rel="stylesheet" href="{{ asset('css/cus_bal.css') }}">
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

<div class="main">       
   <div class="profile-content">
      <div class="balance-container">
            <div class="balance-card">
                <div class="balance-header">
                    <h2>Payment Details</h2>
                    @php
                    $total = optional($reservation->bill)->grand_total ?? 0;
                    $downpayment = $total * 0.5;
                    @endphp
                </div>
                @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-none pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
               <div class="balance-content">
                  <div class="payment-summary">
                     <div class="w-full bg-gray-50 border border-gray-200 border-dashed rounded-lg p-4">
                         <h6 class="text-base text-center font-semibold text-gray-700 mb-2">Payment Summary</h6>
                         <ul class="text-base text-gray-800 space-y-1 mb-2">
                            @foreach ($reservation->reservedAmenities as $reserved)
                                <li class="flex justify-between text-base">
                                    <span>{{ $reserved->amenity->name }}</span>
                                    <span class="font-bold">₱{{ number_format($reserved->amenity->price, 2) }}</span>
                                </li>
                            @endforeach
                         </ul>
                     
                         <hr class="my-2 border-gray-300">
                     
                         <div class="flex justify-between text-base text-gray-700">
                             <strong class="">Total:</strong>
                             <span class="font-bold">₱{{ number_format($total, 2) }}</span>
                         </div>
                     
                         <div class="flex justify-between text-base text-gray-700">
                             <strong class="">Down Payment (50%):</strong>
                             <span class=" font-bold">₱{{ number_format($downpayment, 2) }}</span>
                         </div>
                     </div>
                 </div>
                  
                  <div class="payment-methods">
                     <form class="payment-instructions" action="{{ route('customer.downpayment.store', $reservation->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <h4>1. Pay via GCash</h4>
                        <p> Use the GCash number below to send your payment:
                        <p>
                        <p><a href="#">09151288083 - WAVES Beach Resort</a></p>

                        <h4>2. Provide Payment Details</h4>
                        <p class="upload-instruction">After completing the payment, please enter the following
                           information of the Gcash account you used to proceed with your reservation
                           verification:</p>
                        <label for="ref_number" class="upload-instruction ms-2 my-3">Enter Referene Number:</label>
                        <input type="text" name="ref_number" placeholder="Enter reference number"id="ref_number" class="w-full border border-gray-300 rounded px-3 py-2" required>

                        <h4>3. Upload Proof of Payment</h6>
                        <p class="upload-instruction">Upload the screenshot of your Gcash payment receipt:</p>
                        <input type="file" name="payment_proof" id="payment_proof" accept="image/*" required
                              onchange="previewImage(event)"
                              class="mt-1 block w-full text-xs text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                           <div id="imagePreview" class="mt-3 hidden">
                              <p class="text-xs text-gray-700 mb-1">Preview:</p>
                              <img id="preview" src=""
                                 class="max-w-xs rounded-lg border border-gray-300 shadow-sm"
                                 alt="Image Preview">
                           </div>
                        <div class="button-container mt-4">
                            <button class="pay-button" type="submit" id="pay-button">
                                <i class="fas fa-check"></i> Pay Now
                             </button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
      </div>
   </div>
</div>
<script>
   function previewImage(event) {
       const input = event.target;
       const preview = document.getElementById('preview');
       const previewContainer = document.getElementById('imagePreview');

       if (input.files && input.files[0]) {
           const reader = new FileReader();

           reader.onload = function (e) {
               preview.src = e.target.result;
               previewContainer.classList.remove('hidden');
           }

           reader.readAsDataURL(input.files[0]);
       }
   }

   document.addEventListener("DOMContentLoaded", function () {
       const form = document.querySelector('.payment-instructions');
       const modal = document.getElementById('confirmationModal');
       const confirmBtn = document.getElementById('confirmBtn');
       const cancelBtn = document.getElementById('cancelBtn');

       let submitEvent;

       form.addEventListener('submit', function (e) {
           e.preventDefault();
           submitEvent = e;
           modal.classList.remove('hidden');
       });

       confirmBtn.addEventListener('click', function () {
           modal.classList.add('hidden');
           form.removeEventListener('submit', handleSubmit);
           form.submit();
       });

       cancelBtn.addEventListener('click', function () {
           modal.classList.add('hidden');
       });

       function handleSubmit(e) {
           e.preventDefault();
           modal.classList.remove('hidden');
       }
   });
</script>
</main>
<!-- Confirmation Modal -->
<div id="confirmationModal"
class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] hidden">
<div class="bg-white rounded-lg p-6 shadow-lg w-11/12 max-w-sm">
<h2 class="text-lg font-semibold mb-3 text-gray-800">Confirm Submission</h2>
<p class="text-sm text-gray-600 mb-4">Are you sure you want to submit your down payment?</p>
<div class="flex justify-end space-x-3">
<button id="cancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm">Cancel</button>
<button id="confirmBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Yes,
   Submit</button>
</div>
</div>
</div>

</body>

</html>

