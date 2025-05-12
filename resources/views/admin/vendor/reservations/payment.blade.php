@extends('admin.vendor.reservation')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/cus_bal.css') }}">
@endsection

@section('reservation-content')           
   <div class="profile-content">
      <div class="balance-container">
            <div class="balance-card">
               <div class="balance-header">
                  <h2>Payment Details</h2>
                  @php
                  $total = optional($reservation->bill)->grand_total ?? 0;
                  $downpayment = $total * 0.5;
                  @endphp

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
               </div>
               
               <div class="balance-content">
                  <div class="reservation-details">
                        <div class="detail-group">
                           <span class="detail-label">Reserved By:</span>
                           <span class="detail-value">{{  $reservation->customer->name }}</span>
                        </div>
                        
                        <div class="detail-group">
                           <span class="detail-label">Reservation Date:</span>
                           <span class="detail-value">{{ \Carbon\Carbon::parse($reservation->date)->format('m-d-Y') }}</span>
                        </div>
                        
                        <div class="detail-group">
                           <span class="detail-label">Time:</span>
                           <span class="detail-value">{{ \Carbon\Carbon::parse($reservation->startTime)->format('g:i A') }} - {{ \Carbon\Carbon::parse($reservation->endTime)->format('g:i A') }}</span>
                        </div>
                        
                        <div class="detail-group">
                           <span class="detail-label">Reservation ID:</span>
                           <span class="detail-value">{{ $reservation->id }}</span>
                        </div>
                  </div>

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
                     <form class="payment-instructions" action="{{ route('admin.vendor.process-payment')}}" method="POST" enctype="multipart/form-data" onsubmit="return validateSelection()">
                        @csrf
                           <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                            <input type="hidden" name="bill_id" value="{{ optional($reservation->bill)->id }}">
                            <input type="hidden" name="status" value="verified" hidden>

                            <label for="payment_amount" class="upload-instruction my-3">Enter Payment Amount:</label>
                            <input type="number" name="payment_amount" id="payment_amount" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <div id="error-message" class="text-red-600 text-sm mb-2 hidden"></div>
                              <!-- Error messages will be displayed here -->
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
@endsection

@section('scripts')
<script>
    function validateSelection() {
        const paymentAmount = document.getElementById('payment_amount').value;
        const button = document.getElementById("pay-button");
        const errorMessageContainer = document.getElementById("error-message");
        const total = {{ $total }};
        const downpayment = total * 0.5;
        let errorMessages = [];

        if (parseFloat(paymentAmount) < downpayment) {
            errorMessages.push('Payment amount must be at least 50% of the total.');
        }

        // 🔒 Disable the button to prevent multiple submissions
         if (button) {
               button.disabled = true;
               button.textContent = "Processing..."; // Optional
         }
         
         if (errorMessages.length > 0) {
            errorMessageContainer.style.display = "block";
            errorMessageContainer.innerHTML = errorMessages.join("<br>");
            return false;
         }

         errorMessageContainer.style.display = "none";
         return true;
    }
</script>
@endsection