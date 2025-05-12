<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\DownPayment;
use App\Models\Balance;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReservedAmenity;

class DownpaymentController extends Controller
{
    public function showReceipt(Reservation $reservation)
    {
        // Eager load bill (singular), not bills (plural)
        $reservation->load('reservedAmenities.amenity', 'bill');

        // Access the bill directly from the relationship
        $bill = $reservation->bill;

        if (!$bill) {
            return back()->withErrors(['bill' => 'No billing information found. Please contact support.']);
        }

        return view('customer.downpayment', compact('reservation', 'bill'));
    }
    public function billing(Reservation $reservation)
    {
        $customer = auth()->user();
        // Eager load bill (singular), not bills (plural)
        $reservation->load('reservedAmenities.amenity', 'bill');

        // Access the bill directly from the relationship
        $bill = $reservation->bill;

        if (!$bill) {
            return back()->withErrors(['bill' => 'No billing information found. Please contact support.']);
        }

        return view('customer.payment', compact('customer', 'reservation', 'bill'));
    }

    public function storePayment(Request $request, $reservationId)
    {
        $request->validate([
            'ref_number' => 'required|string|max:20',
            'payment_proof' => 'required|image|max:2048',
        ]);

        $reservation = Reservation::with('reservedAmenities.amenity', 'bill')->findOrFail($reservationId);
        $bill = $reservation->bill;

        if (!$bill) {
            return back()->withErrors(['bill' => 'No billing information found. Please contact support.']);
        }

        $date = $reservation->date;
        $startTime = $reservation->startTime;
        $endTime = $reservation->endTime;

        // Get amenity IDs selected in this reservation
        $amenityIds = $reservation->reservedAmenities->pluck('amenity_id');

        // Check for conflicting amenities with existing downpayment on same date and time
        $conflictingAmenities = [];
        foreach ($reservation->reservedAmenities as $reservedAmenity) {
            $conflict = ReservedAmenity::where('amenity_id', $reservedAmenity->amenity_id)
                ->whereHas('reservation', function ($query) use ($reservation) {
                    $query->where('date', $reservation->date)
                        ->where('startTime', '<', $reservation->endTime)
                        ->where('endTime', '>', $reservation->startTime)
                        ->where('id', '!=', $reservation->id);
                })
                ->whereHas('reservation.downpayment', function ($query) {
                    $query->whereIn('status', ['pending', 'verified']);
                })
                ->with('amenity')
                ->first();

            if ($conflict) {
                $conflictingAmenities[] = $reservedAmenity->amenity->name;
            }
        }

        if (!empty($conflictingAmenities)) {
            return back()->withErrors([
                'conflict' => 'The following amenities are already reserved with downpayments on this date and time: ' .
                    implode(', ', $conflictingAmenities)
            ]);
        }

        // Store uploaded image proof
        $imagePath = $request->file('payment_proof')->store('proofs', 'public');

        // Create the new downpayment
        $downpayment = DownPayment::create([
            'id' => Str::uuid(),
            'res_num' => $reservation->id,
            'bill_id' => $bill->id,
            'amount' => null,
            'ref_num' => $request->ref_number,
            'img_proof' => $imagePath,
            'date' => now(),
            'status' => 'pending',
            'verified_by' => null,
        ]);

        // Update or create balance record
        $existingBalance = Balance::where('bill_id', $bill->id)->first();

        if ($existingBalance) {
            $existingBalance->update([
                'dp_id' => $downpayment->id,
            ]);
        }

        return redirect()->route('customer.reservation')->with('success', 'Downpayment submitted successfully!');
    }

}
