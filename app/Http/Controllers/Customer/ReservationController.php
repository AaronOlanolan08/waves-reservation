<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservedAmenity;
use App\Models\Amenities;
use App\Models\DownPayment;
use App\Models\Bill;
use App\Mail\PaymentReminderMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function create(Request $request)
    {
        // Fetch only active cottages and tables
        $cottages = Amenities::where('type', 'cottage')->where('is_active', 1)->get();
        $tables = Amenities::where('type', 'table')->where('is_active', 1)->get();

        // If date is selected, filter out the reserved amenities for that date
        if ($request->has('date')) {
            $date = $request->date;

            // Get all reserved amenities for the selected date
            $reservedCottages = ReservedAmenity::join('reservations', 'reserved_amenity.res_num', '=', 'reservations.id')
                ->where('reservations.date', $date)
                ->where('reservations.status', '!=', 'cancelled') // Make sure we are only considering active reservations
                ->whereHas('amenity', function ($query) {
                    $query->where('type', 'cottage');
                })
                ->pluck('reserved_amenity.amenity_id')
                ->toArray();

            $reservedTables = ReservedAmenity::join('reservations', 'reserved_amenity.res_num', '=', 'reservations.id')
                ->where('reservations.date', $date)
                ->where('reservations.status', '!=', 'cancelled')
                ->whereHas('amenity', function ($query) {
                    $query->where('type', 'table');
                })
                ->pluck('reserved_amenity.amenity_id')
                ->toArray();

            // Filter out the reserved cottages and tables from the available list
            $cottages = $cottages->whereNotIn('id', $reservedCottages);
            $tables = $tables->whereNotIn('id', $reservedTables);
        }
        return view('customer.reservation', compact('cottages', 'tables'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to make a reservation.');
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i|after:startTime',
            'cottage' => 'nullable|exists:amenities,id',
            'tables' => 'nullable|exists:amenities,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $startTime = Carbon::parse($request->startTime)->format('H:i:s');
        $endTime = Carbon::parse($request->endTime)->format('H:i:s');

        $reservation = Reservation::create([
            'id' => Str::uuid(),
            'customer_id' => Auth::id(),
            'date' => $request->date,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'status' => 'pending',
        ]);

        // Save selected cottage if present
        if ($request->has('cottages')) {
            foreach ($request->cottages as $cottageId) {
                if (!empty($cottageId)) {
                    ReservedAmenity::create([
                        'res_num' => $reservation->id,
                        'amenity_id' => $cottageId,
                    ]);
                }
            }
        }

        // Save selected table if present
        if ($request->has('tables')) {
            foreach ($request->input('tables') as $tableId) {
                if (!empty($cottageId)) {
                    ReservedAmenity::create([
                        'res_num' => $reservation->id,
                        'amenity_id' => $tableId,
                    ]);
                }
            }
        }


        $total = 0;

        // Sum selected cottages
        if ($request->filled('cottages')) {
            foreach ($request->cottages as $cottageId) {
                $amenity = Amenities::find($cottageId);
                if ($amenity) {
                    $total += $amenity->price;
                }
            }
        }

        // Sum selected tables
        if ($request->filled('tables')) {
            foreach ($request->tables as $tableId) {
                $amenity = Amenities::find($tableId);
                if ($amenity) {
                    $total += $amenity->price;
                }
            }
        }

        // Create the bill
        Bill::create([
            'id' => Str::uuid(),
            'res_num' => $reservation->id,
            'grand_total' => $total,
            'date' => Carbon::now(),
            'status' => 'unpaid',
        ]);

        return redirect()->route('customer.downpayment.show', $reservation);
    }

    public function view_reservations()
    {
        $customer = auth()->user();

        $allReservations = $customer->reservations()
            ->with(['reservedAmenities.amenity', 'bill.balance', 'downPayment'])
            ->get();

        $allReservations->each(function ($reservation) {
            $bill = $reservation->bill;

            $grandTotal = optional($bill)->grand_total ?? 0;

            // Sum only verified down payments
            $paidAmount = DownPayment::where('res_num', $reservation->id)
                ->where('status', 'verified')
                ->sum('amount');

            $reservation->paidAmount = $paidAmount;
            $reservation->grandTotal = $grandTotal;
            $reservation->balance = $grandTotal - $paidAmount;
        });

        $pendingReservationsWithDP = $customer->reservations()
            ->where('status', 'pending')
            ->whereHas('downPayment')
            ->with(['reservedAmenities.amenity', 'downPayment'])
            ->get();

        $pendingReservationsWithoutDP = $customer->reservations()
            ->where('status', 'pending')
            ->whereDoesntHave('downPayment') // no down payment
            ->with(['reservedAmenities.amenity', 'bill']) // no need to load downPayment
            ->get();

        $reservationsWithFullyPaidBills = $customer->reservations()
            ->where('status', 'verified')
            ->whereHas('bill', function ($query) {
                $query->where('status', 'paid');
            })
            ->with(['reservedAmenities.amenity', 'bill'])
            ->get();

        $reservationsWithPartialBills = $customer->reservations()
            ->where('status', 'verified')
            ->whereHas('bill', function ($query) {
                $query->where('status', 'partially paid');
            })
            ->with(['reservedAmenities.amenity', 'bill'])
            ->get();


        $paidReservations = $reservationsWithFullyPaidBills->merge($reservationsWithPartialBills);

        $cancelledReservations = $allReservations->where('status', 'cancelled');
        $completedReservations = $allReservations->where('status', 'completed');
        $verifiedReservations = $allReservations->where('status', 'verified');
        $invalidReservations = $allReservations->where('status', 'invalid');

        $redReservations = $cancelledReservations->merge($invalidReservations);
        $pendingReservations = $pendingReservationsWithDP->merge($pendingReservationsWithoutDP);

        return view('customer.reservation_records', compact(
            'customer',
            'pendingReservations',
            'cancelledReservations',
            'completedReservations',
            'invalidReservations',
            'allReservations',
            'verifiedReservations',
            'redReservations',
            'paidReservations'
        ));
    }

    public function checkAvailability(Request $request)
    {
        $date = $request->input('date');
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');

        // Fetch all reserved amenities for the given date and overlapping time range
        $reservedAmenities = ReservedAmenity::whereHas('reservation', function ($query) use ($date, $startTime, $endTime) {
            $query->where('date', $date)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('startTime', [$startTime, $endTime])
                        ->orWhereBetween('endTime', [$startTime, $endTime])
                        ->orWhere(function ($query) use ($startTime, $endTime) {
                            $query->where('startTime', '<=', $startTime)
                                ->where('endTime', '>=', $endTime);
                        });
                });
        })->pluck('amenity_id');

        // Fetch available cottages and tables
        $availableCottages = Amenities::where('type', 'cottage')
            ->where('is_active', true)
            ->whereNotIn('id', $reservedAmenities)
            ->get();

        $availableTables = Amenities::where('type', 'table')
            ->where('is_active', true)
            ->whereNotIn('id', $reservedAmenities)
            ->get();

        return response()->json([
            'availableCottages' => $availableCottages,
            'availableTables' => $availableTables,
        ]);
    }

    public function cancel_reservation(Request $request, $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        if ($reservation->status !== 'cancelled') {
            $reservation->status = 'cancelled';
            $reservation->save();

            return response()->json(['message' => 'Reservation cancelled successfully.']);
        }

        return response()->json(['message' => 'Reservation already cancelled.'], 400);
    }

    public function sendReminder(Request $request)
    {
        try {
            $request->validate([
                'reservation_id' => 'required|exists:reservations,id',
            ]);

            $reservation = Reservation::with(['customer', 'bill', 'downPayment'])->findOrFail($request->reservation_id);

            if ($reservation->customer && $reservation->customer->email) {
                // Format date and time
                $reservation->formattedDate = Carbon::parse($reservation->date)->format('m-d-Y');
                $reservation->formattedStartTime = Carbon::parse($reservation->startTime)->format('h:i A'); // 12-hour format
                $reservation->formattedEndTime = Carbon::parse($reservation->endTime)->format('h:i A'); // 12-hour format

                // Calculate grandTotal, paidAmount, and balance
                $grandTotal = optional($reservation->bill)->grand_total ?? 0;

                $paidAmount = DownPayment::where('res_num', $reservation->id)
                    ->where('status', 'verified')
                    ->sum('amount');

                $reservation->grandTotal = $grandTotal;
                $reservation->paidAmount = $paidAmount;
                $reservation->balance = $grandTotal - $paidAmount;

                // Send the email
                Mail::to($reservation->customer->email)->send(new PaymentReminderMail($reservation));

                return response()->json(['message' => 'Reminder email sent successfully!']);
            }

            return response()->json(['message' => 'Customer email not available.'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while sending the reminder.'], 500);
        }
    }
}