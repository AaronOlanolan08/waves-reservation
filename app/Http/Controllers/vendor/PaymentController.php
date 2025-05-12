<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\DownPayment;
use App\Models\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0',
            'reservation_id' => 'required|string|regex:/^RES-\d{8}\d{3}$/', 
            'bill_id' => 'required|uuid',
            'status' => 'required|string|in:verified,invalid,completed',
        ]);
    
        try {
            DB::beginTransaction();
    
            $reservation = Reservation::with('bill')->findOrFail($request->reservation_id);
            $bill = $reservation->bill;
    
            if (!$bill || $bill->id !== $request->bill_id) {
                return back()->withErrors(['bill' => 'Invalid bill selected for this reservation.']);
            }
    
            $paymentAmount = $request->payment_amount;
            $grandTotal = $bill->grand_total;
            $downPaymentAmount = $grandTotal * 0.5;
    
            // 1. Check for existing PENDING down payment
            $existingPendingDP = DownPayment::where('res_num', $reservation->id)
                ->where('status', 'pending')
                ->latest('date')
                ->first();
    
            if ($existingPendingDP) {
                // Update the pending DP to verified with new amount
                $existingPendingDP->update([
                    'amount' => $paymentAmount,
                    'status' => 'verified',
                    'verified_by' => Auth::id(),
                    'date' => now(),
                ]);
                $activeDownPayment = $existingPendingDP;
            } else {
                // Create new DP if no pending exists or previous was already verified
                $activeDownPayment = DownPayment::create([
                    'id' => Str::uuid(),
                    'bill_id' => $bill->id,
                    'res_num' => $reservation->id,
                    'amount' => $paymentAmount,
                    'ref_num' => 'N/A',
                    'img_proof' => null,
                    'date' => now(),
                    'status' => 'verified',
                    'verified_by' => Auth::id(),
                ]);
            }
    
            // 2. Get total of all valid DPs (excluding invalid ones)
            $existingDownPayments = DownPayment::where('res_num', $reservation->id)
                ->where('status', '!=', 'invalid')
                ->sum('amount');
    
            $computedBalance = $grandTotal - $existingDownPayments;
    
            // 3. Get or create balance for this bill, and link latest DP
            $balance = Balance::firstOrNew([
                'bill_id' => $bill->id,
            ]);
    
            $balance->dp_id = $activeDownPayment->id;
            $balance->balance = $computedBalance;
            $balance->status = $computedBalance <= 0 ? 'paid' : 'partially paid';
            $balance->received_by = Auth::id();
            $balance->save();
    
            // 4. Update bill status
            if ($computedBalance <= 0) {
                $bill->status = 'paid';
            } elseif ($existingDownPayments >= $downPaymentAmount) {
                $bill->status = 'partially paid';
            } else {
                $bill->status = 'unpaid';
            }
    
            $bill->save();
    
            // 5. Handle "invalid" status from request
            if ($request->status === 'invalid') {
                $activeDownPayment->update(['status' => 'invalid']);
            }
    
            // 6. Auto-verify reservation if minimum 50% payment is met
            if ($reservation->status === 'pending' && $existingDownPayments >= $downPaymentAmount) {
                $reservation->update(['status' => 'verified']);
            }
    
            DB::commit();
    
            return redirect()->route('admin.vendor.reservation_calendar')->with(['success' => 'Cash payment recorded successfully.']);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

public function invalidPayment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'reservation_id' => 'required|exists:reservations,id',
        'dp_id' => 'required|exists:down_payment,id', 
        'bill_id' => 'required|exists:bills,id',
        'status' => 'required|in:invalid',
    ]);

    $validated = $validator->validated();

    try {
        DB::beginTransaction();

        // Update down payment status
        $downpayment = DownPayment::findOrFail($validated['dp_id']);
        $downpayment->update([
            'status' => 'invalid',
            'verified_by' => Auth::id()
        ]);

        DB::commit();

        return redirect()->back()
            ->with('error', 'Payment marked as invalid.')
            ->with('invalid_payment', true);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Failed to mark payment as invalid. Please try again.');
    }
}

}
