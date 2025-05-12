<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Amenities;
use App\Models\ReservedAmenity;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\DownPayment;
use App\Models\Bill;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationRecordController extends Controller
{
    public function view_balance()
{
    $vendor = auth('admin')->user();

    $reservations = Reservation::with([
            'customer',
            'reservedAmenities.amenity',
            'bill.balance',
            'downPayment'
        ])
        ->whereIn('status', ['pending', 'verified'])
        ->whereHas('bill', function ($query) {
            $query->whereIn('status', ['partially paid', 'unpaid']);
        })
        ->get();

    $reservations->each(function ($reservation) {
        $bill = $reservation->bill;

        $grandTotal = optional($bill)->grand_total ?? 0;

        // Sum all verified down payments for this reservation
        $paidAmount = DownPayment::where('res_num', $reservation->id)
            ->whereIn('status', ['verified'])
            ->sum('amount');

        $reservation->paidAmount = $paidAmount;
        $reservation->grandTotal = $grandTotal;
        $reservation->balance = $grandTotal - $paidAmount;

        $reservation->downPaymentImageUrl = $reservation->downPayment?->img_proof
            ? asset('storage/' . $reservation->downPayment->img_proof)
            : null;

        $reservation->downpayment_status = $reservation->downPayment->status ?? null;
    });

        return view('admin.vendor.remainingbal', compact('reservations'));
    }
    
    public function view_all_reservations()
    {
        $vendor = auth('admin')->user();
    
        $reservations = Reservation::with([
                'customer',
                'reservedAmenities.amenity',
                'bill.balance',
                'downPayment'
            ])
            ->get();
    
        // Add computed attributes
        $reservations->each(function ($reservation) {
            $bill = $reservation->bill;
            $grandTotal = optional($bill)->grand_total ?? 0;
    
            $paidAmount = DownPayment::where('res_num', $reservation->id)
                            ->where('status', 'verified')
                            ->sum('amount');
    
            $reservation->paidAmount = $paidAmount;
            $reservation->grandTotal = $grandTotal;
            $reservation->balance = $grandTotal - $paidAmount;
        });
    
        // Filter reservations from the collection
        $pendingReservationsWithDP = $reservations->filter(function ($res) {
            return $res->status === 'pending' && $res->downPayment;
        });
    
        $pendingReservationsWithoutDP = $reservations->filter(function ($res) {
            return $res->status === 'pending' && !$res->downPayment;
        });
    
        $reservationsWithFullyPaidBills = $reservations->filter(function ($res) {
            return $res->status === 'verified' && optional($res->bill)->status === 'paid';
        });
    
        $reservationsWithPartialBills = $reservations->filter(function ($res) {
            return $res->status === 'verified' && optional($res->bill)->status === 'partially paid';
        });
    
        $paidReservations = $reservationsWithFullyPaidBills->merge($reservationsWithPartialBills);
    
        $cancelledReservations = $reservations->where('status', 'cancelled');
        $completedReservations = $reservations->where('status', 'completed');
        $verifiedReservations = $reservations->where('status', 'verified');
        $invalidReservations = $reservations->where('status', 'invalid');
    
        $redReservations = $cancelledReservations->merge($invalidReservations);
        $pendingReservations = $pendingReservationsWithDP->merge($pendingReservationsWithoutDP);
        $allReservations = $reservations;

        $userId = Auth::id();
        $user = Admin::find($userId);

        if ($user->role == 'Manager') {
            return view('admin.manager.reservations.all_reservations', compact(
                'vendor',
                'pendingReservations',
                'cancelledReservations',
                'completedReservations',
                'invalidReservations',
                'allReservations',
                'verifiedReservations',
                'redReservations',
                'paidReservations',
                'reservations'
            ));
        } elseif ($user->role == 'Vendor') {
            return view('admin.vendor.reservations.reservation_records', compact(
                'vendor',
                'pendingReservations',
                'cancelledReservations',
                'completedReservations',
                'invalidReservations',
                'allReservations',
                'verifiedReservations',
                'redReservations',
                'paidReservations',
                'reservations'
            ));
        }

         // Default case
        return view('admin.vendor.reservations.reservation_records', compact(
            'vendor',
            'pendingReservations',
            'cancelledReservations',
            'completedReservations',
            'invalidReservations',
            'allReservations',
            'verifiedReservations',
            'redReservations',
            'paidReservations',
            'reservations'
        ));
    }

    public function view_reservation()
    {
        $userId = Auth::id();
        $user = Admin::find($userId);

        if ($user->role == 'Manager') {
            return view('admin.manager.reservations.reservation_list');
        } elseif ($user->role == 'Vendor') {
            return view('admin.vendor.reservation_calendar');
        }

        return redirect()->route('login')->with('error', 'Unauthorized access.');
    
    }

    public function getEvents()
    {
        try {
            // will only show reservation in calendar that has downpayment
            $reservations = Reservation::with(['customer', 'reservedAmenities.amenity', 'bill', 'downPayment'])
                ->where(function ($query) {
                    $query->whereHas('bill', function ($q) {
                        $q->where('status', 'partially paid');
                    })
                    ->orWhereHas('downPayment');
                })
                ->get();
    
            $events = $reservations->map(function ($reservation) {
                $statusColors = [
                    'verified' => ['bg' => '#def2df', 'border' => '#33884d'],
                    'pending' => ['bg' => '#fdf0bf', 'border' => '#a96715'],
                    'cancelled' => ['bg' => '#fce1e1', 'border' => '#c23a3a'],
                    'invalid' => ['bg' => '#fce1e1', 'border' => '#c23a3a'],
                    'completed' => ['bg' => '#f3ebed', 'border' => '#475569']
                ];
    
                $status = strtolower($reservation->status);
                $color = $statusColors[$status] ?? ['bg' => '#3b82f6', 'border' => '#1e40af'];
    
                $paidAmount = DownPayment::where('res_num', $reservation->id)
                            ->whereIn('status', ['verified'])
                            ->sum('amount');
    
                return [
                    'id' => $reservation->id,
                    'title' => ($reservation->customer->name ?? 'No Name') . ' | ' . 
                        Carbon::parse($reservation->startTime ?? '00:00')->format('g:i A'). ' - ' .
                        Carbon::parse($reservation->endTime ?? '00:00')->format('g:i A'),
                    'start' => $reservation->date,
                    'end' => $reservation->date,
                    'status' => $reservation->status,
                    'backgroundColor' => $color['bg'],
                    'borderColor' => $color['border'],
                    'extendedProps' => [
                        'customer_name' => $reservation->customer->name ?? 'Unknown',
                        'phone_number' => $reservation->customer->number ?? 'Not Available',
                        'date' => $reservation->date,
                        'start_time' => Carbon::parse($reservation->startTime ?? '00:00 AM')->format('g:i A'),
                        'end_time' => Carbon::parse($reservation->endTime ?? '00:00 PM')->format('g:i A'),
                        'amenities' => $reservation->reservedAmenities->map(function ($reservedAmenity) {
                            return [
                                'name' => $reservedAmenity->amenity->name ?? 'Unknown',
                                'price' => $reservedAmenity->amenity->price ?? 0,
                            ];
                        })->toArray(),
                        'status' => $reservation->status,
                        'reservation_id' => $reservation->id,
                        'dp_id' => $reservation->downPayment->id ?? null,
                        'bill_id' => $reservation->bill->id ?? null,
                        'bill_status' => $reservation->bill->status ?? null,
                        'total' => $reservation->bill->grand_total ?? 0,
                        'paid_amount' => $paidAmount,
                        'downpayment' => ($reservation->bill->grand_total ?? 0) * 0.5,
                        'downpayment_status' => $reservation->downPayment->status ?? null,
                        'downpayment_image' => $reservation->downPayment->img_proof ? asset('storage/' . $reservation->downPayment->img_proof) : null,
                        'ref_num' => $reservation->downPayment->ref_num ?? 'N/A',
                    ]
                ];
            });
    
            return response()->json($events);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while processing the request.', 'details' => $e->getMessage()], 500);
        }
    }

    public function amenitiesAvailability(Request $request)
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
                 ->where(function ($q) {
                    $q->where('reservations.status', 'verified')
                    ->orWhereHas('downPayment', function ($q2) {
                        $q2->whereIn('status', ['verified', 'pending']);
                    });
                })
                ->whereHas('amenity', function ($query) {
                    $query->where('type', 'cottage');
                })
                ->pluck('reserved_amenity.amenity_id')
                ->toArray();

            $reservedTables = ReservedAmenity::join('reservations', 'reserved_amenity.res_num', '=', 'reservations.id')
                ->where('reservations.date', $date)
                ->where(function ($q) {
                    $q->where('reservations.status', 'verified')
                    ->orWhereHas('downPayment', function ($q2) {
                        $q2->whereIn('status', ['verified', 'pending']);
                    });
                })
                ->whereHas('amenity', function ($query) {
                    $query->where('type', 'table');
                })
                ->pluck('reserved_amenity.amenity_id')
                ->toArray();

            // Filter out the reserved cottages and tables from the available list
            $cottages = $cottages->whereNotIn('id', $reservedCottages);
            $tables = $tables->whereNotIn('id', $reservedTables);
        }
        return view('admin.vendor.reservations.walk_in', compact('cottages', 'tables'));
    }

    public function custom_WalkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'number' => 'required|regex:/^[0-9]{11}$/',
            'date' => 'required|date',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i|after:startTime',
            'cottage' => 'nullable|exists:amenities,id',
            'tables' => 'nullable|exists:amenities,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create or find the customer
        $customer = Customer::Create(
            [
                'id' => Str::uuid(),
                'name' => $request->name,
                'number' => $request->number,
                'email'=> null,
                'password' => null,
            ]
        );

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

        // Create the reservation
        $reservation = Reservation::create([
            'id' => $newId,
            'customer_id' => $customer->id,
            'date' => $request->date,
            'startTime' => Carbon::parse($request->startTime)->format('H:i:s'),
            'endTime' => Carbon::parse($request->endTime)->format('H:i:s'),
            'status' => 'pending',
        ]);

        // Save selected cottages
        if ($request->has('cottages')) {
            foreach ($request->cottages as $cottageId) {
                ReservedAmenity::create([
                    'res_num' => $reservation->id,
                    'amenity_id' => $cottageId,
                ]);
            }
        }

        // Save selected tables
        if ($request->has('tables')) {
            foreach ($request->tables as $tableId) {
                ReservedAmenity::create([
                    'res_num' => $reservation->id,
                    'amenity_id' => $tableId,
                ]);
            }
        }

        // Calculate the total price
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

        return redirect()->route('vendor.payment.page', ['reservation' => $reservation->id]);
    }
}
