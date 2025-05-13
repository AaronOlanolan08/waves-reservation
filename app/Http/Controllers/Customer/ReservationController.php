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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    public function updateReservation(Request $request)
    {
        // Step 1: Validate request
        $request->validate([
            'res_num' => 'required|exists:reservations,id',
            'date' => 'required|date',
            'starttime' => 'required|date_format:H:i',
            'endtime' => 'required|date_format:H:i|after:starttime',
            'cottages' => 'array',
            'tables' => 'array',
        ]);

        $resNum = $request->res_num;
        $date = $request->date;

        // Step 2: Normalize time format
        $startTime = Carbon::createFromFormat('H:i', $request->starttime)->format('H:i'); // Ensure H:i format
        $endTime = Carbon::createFromFormat('H:i', $request->endtime)->format('H:i'); // Ensure H:i format

        // Step 3: Combine selected amenities
        $selectedCottages = $request->input('cottages', []);
        $selectedTables = $request->input('tables', []);
        $selectedAmenities = array_merge($selectedCottages, $selectedTables);

        // Step 4: Conflict check
        $reservedAmenities = ReservedAmenity::join('reservations', 'reserved_amenity.res_num', '=', 'reservations.id')
            ->where('reservations.date', $date)
            ->where(function ($q) {
                $q->where('reservations.status', 'verified')
                    ->orWhereHas('downPayment', function ($q2) {
                        $q2->whereIn('status', ['verified', 'pending']);
                    });
            })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('reservations.starttime', '<', $endTime)
                    ->where('reservations.endtime', '>', $startTime);
            })
            ->where('reservations.status', '!=', 'cancelled')
            ->where('reservations.id', '!=', $resNum)
            ->pluck('reserved_amenity.amenity_id')
            ->toArray();

        $conflicts = array_intersect($selectedAmenities, $reservedAmenities);

        if (!empty($conflicts)) {
            // Get the names of the conflicted amenities
            $conflictedAmenities = Amenities::whereIn('id', $conflicts)->pluck('name')->toArray();

            return redirect()->back()
                ->withErrors(['conflict' => 'The following amenities are already reserved during the selected date and time: ' . implode(', ', $conflictedAmenities)])
                ->withInput();
        }

        // Step 5: Update reservation details
        $reservation = Reservation::findOrFail($resNum);
        $reservation->date = $date;
        $reservation->starttime = $startTime;  // Store in H:i format
        $reservation->endtime = $endTime;      // Store in H:i format
        $reservation->save();

        // Step 6: Update reserved amenities
        ReservedAmenity::where('res_num', $resNum)->delete();

        foreach ($selectedAmenities as $amenityId) {
            ReservedAmenity::create([
                'res_num' => $resNum,
                'amenity_id' => $amenityId,
            ]);
        }

        // Step 7: Redirect with success message
        return redirect()->route('customer.reservation.records')
            ->with('success', 'Reservation updated successfully.');
    }

    public function edit_amenities(Request $request)
    {
        $selectedCottages = [];
        $selectedTables = [];

        // Get selected amenities for current reservation
        if ($request->has('res_num')) {
            $resNum = $request->res_num;

            $selectedAmenities = ReservedAmenity::where('res_num', $resNum)->with('amenity')->get();

            $selectedCottages = $selectedAmenities
                ->filter(fn($item) => $item->amenity->type === 'cottage')
                ->pluck('amenity_id')
                ->toArray();

            $selectedTables = $selectedAmenities
                ->filter(fn($item) => $item->amenity->type === 'table')
                ->pluck('amenity_id')
                ->toArray();
        }

        // Filter based on date/time
        if ($request->has('date') && $request->has('starttime') && $request->has('endtime')) {
            $date = $request->date;
            $startTime = $request->starttime;
            $endTime = $request->endtime;

            // Get reserved amenity IDs for overlapping time
            $reservedAmenities = ReservedAmenity::join('reservations', 'reserved_amenity.res_num', '=', 'reservations.id')
                ->where('reservations.date', $date)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('reservations.starttime', '<', $endTime)
                        ->where('reservations.endtime', '>', $startTime);
                })
                ->where('reservations.status', '!=', 'cancelled')
                ->when($request->res_num, function ($query, $resNum) {
                    return $query->where('reservations.id', '!=', $resNum);
                })
                ->pluck('reserved_amenity.amenity_id')
                ->toArray();

            // Cottages: active, not in reserved list or currently selected
            $cottages = Amenities::where('type', 'cottage')
                ->where('is_active', 1)
                ->where(function ($query) use ($reservedAmenities, $selectedCottages) {
                    $query->whereNotIn('id', $reservedAmenities)
                        ->orWhereIn('id', $selectedCottages);
                })->get();

            // Tables: active, not in reserved list or currently selected
            $tables = Amenities::where('type', 'table')
                ->where('is_active', 1)
                ->where(function ($query) use ($reservedAmenities, $selectedTables) {
                    $query->whereNotIn('id', $reservedAmenities)
                        ->orWhereIn('id', $selectedTables);
                })->get();

        } else {
            // No filter, just get all active amenities
            $cottages = Amenities::where('type', 'cottage')->where('is_active', 1)->get();
            $tables = Amenities::where('type', 'table')->where('is_active', 1)->get();
        }

        return response()->json([
            'cottages' => $cottages->values()->toArray(),
            'tables' => $tables->values()->toArray(),
            'selectedCottages' => $selectedCottages,
            'selectedTables' => $selectedTables,
        ]);
    }

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

    public function createReservation(Request $request)
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

        $currentDate = Carbon::now('Asia/Manila')->format('Ymd');

        // Find latest reservation ID that starts with today's date
        $latestReservation = DB::table('reservations')
            ->where('id', 'like', "RES-{$currentDate}%")
            ->orderByDesc('id')
            ->first();

        // Get next increment number
        $nextNumber = 1;
        if ($latestReservation) {
            // Extract the last 3 digits from the ID
            $lastId = $latestReservation->id;
            $lastNumber = (int) substr($lastId, -3);  // Last 3 digits
            $nextNumber = $lastNumber + 1;
        }

        // Format new ID like RES-20250510001
        $newId = 'RES-' . $currentDate . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $reservation = Reservation::create([
            'id' => $newId,
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

        $pendingReservationsWithDP->each(function ($reservation) {
            $bill = $reservation->bill;

            $grandTotal = optional($bill)->grand_total ?? 0;

            // Sum only verified down payments
            $paidAmount = DownPayment::where('res_num', $reservation->id)
                ->where('status', '!=', 'invalid')
                ->sum('amount');

            $reservation->paidAmount = $paidAmount;
            $reservation->grandTotal = $grandTotal;
            $reservation->balance = $grandTotal - $paidAmount;
        });

        $pendingReservationsWithoutDP = $customer->reservations()
            ->where('status', 'pending')
            ->whereDoesntHave('downPayment') // no down payment
            ->with(['reservedAmenities.amenity', 'bill']) // no need to load downPayment
            ->get();

        $pendingReservationsWithoutDP->each(function ($reservation) {
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

        $reservationsWithFullyPaidBills = $customer->reservations()
            ->where('status', 'verified')
            ->whereHas('bill', function ($query) {
                $query->where('status', 'paid');
            })
            ->with(['reservedAmenities.amenity', 'bill'])
            ->get();

        $reservationsWithFullyPaidBills->each(function ($reservation) {
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

        $reservationsWithPartialBills = $customer->reservations()
            ->where('status', 'verified')
            ->whereHas('bill', function ($query) {
                $query->where('status', 'partially paid');
            })
            ->with(['reservedAmenities.amenity', 'bill'])
            ->get();

        $reservationsWithPartialBills->each(function ($reservation) {
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

        // Fetch reserved amenities from verified reservations with downPayment status pending or verified
        $reservedAmenities = ReservedAmenity::whereHas('reservation', function ($query) use ($date, $startTime, $endTime) {
            $query->where('reservations.date', $date)
                ->where(function ($q) {
                    $q->where('reservations.status', 'verified')
                        ->orWhereHas('downPayment', function ($q2) {
                            $q2->whereIn('status', ['verified', 'pending']);
                        });
                })
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('reservations.startTime', [$startTime, $endTime])
                        ->orWhereBetween('reservations.endTime', [$startTime, $endTime])
                        ->orWhere(function ($query) use ($startTime, $endTime) {
                            $query->where('reservations.startTime', '<=', $startTime)
                                ->where('reservations.endTime', '>=', $endTime);
                        });
                });
        })->pluck('amenity_id');


        // Fetch available cottages and tables (not among reserved and still active)
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

        }

        return redirect()->route('customer.reservation.records')->with('success', 'Reservation cancelled successfully.');
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

    public function about()
    {
        return view('customer.about');
    }
}