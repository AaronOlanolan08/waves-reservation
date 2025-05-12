<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\DownPayment;
use App\Models\Reservation;
use App\Models\Bill;

class AdminDashboardController extends Controller
{
    // public function __construct()
    // {
    //     // Ensures that only authenticated admins can access this controller
    //     $this->middleware('auth:admin');
    // }

    public function create()
    {
        $currentMonth = Carbon::now()->month;

        // Revenue for current month
        $revenue = Bill::whereIn('status', ['paid', 'partially_paid'])
            ->whereMonth('date', $currentMonth)
            ->sum('grand_total');

        // Reservation counts
        $completedReservations = Reservation::where('status', 'completed')
            ->whereMonth('date', $currentMonth)
            ->count();
        $pendingReservations = Reservation::where('status', 'pending')
            ->whereMonth('date', $currentMonth)
            ->count();
        $verifiedReservations = Reservation::where('status', 'verified')
            ->whereMonth('date', $currentMonth)
            ->count();

        // Monthly revenue for line chart
        $monthlyRevenue = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyRevenue[] = Bill::whereIn('status', ['paid', 'partially_paid'])
                ->whereMonth('date', $month)
                ->sum('grand_total');
        }

        $monthlyLabels = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ];

        $monthlyRevenue = [];
        $annualRevenue = 0;

        for ($month = 1; $month <= 12; $month++) {
            $monthlySum = Bill::whereIn('status', ['paid', 'partially_paid'])
                ->whereMonth('date', $month)
                ->sum('grand_total');
            $monthlyRevenue[] = $monthlySum;
            $annualRevenue += $monthlySum;
        }

        $averageMonthlyRevenue = $currentMonth > 0 ? $annualRevenue / $currentMonth : 0;

        return view('admin.dashboard', compact(
            'revenue',
            'completedReservations',
            'pendingReservations',
            'verifiedReservations',
            'monthlyRevenue',
            'monthlyLabels',
            'annualRevenue',
            'averageMonthlyRevenue'
        ));

    }

    public function store(Request $request)
    {

        $this->validator($request->all())->validate();

        $admin = Admin::create([
            'name' => $request->name,
            'number' => $request->number,  // Store the contact number
            'email' => $request->email,
            'password' => Hash::make($request->password),  // Hash the password
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Account created successfully.');
    }


    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'number' => 'required|regex:/^[0-9]{11}$/',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'max:255'],
        ]);
    }

    public function create_admin()
    {
        return view('admin.manager.profile.create_admin');
    }

    public function view_all_reservations()
    {
        $manager = auth('admin')->user();

        $reservations = Reservation::with([
            'customer',
            'reservedAmenities.amenity',
            'bill.balance',
            'downPayment'
        ])
            ->get();

        $reservations->each(function ($reservation) {
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

        // Group reservations
        $pendingReservations = $reservations->where('status', 'pending');
        $cancelledReservations = $reservations->where('status', 'cancelled');
        $completedReservations = $reservations->where('status', 'completed');
        $verifiedReservations = $reservations->where('status', 'verified');
        $invalidReservations = $reservations->where('status', 'invalid');

        $currentReservations = $pendingReservations->merge($verifiedReservations);

        return view('admin.manager.reservations.all_reservations', compact(
            'pendingReservations',
            'cancelledReservations',
            'completedReservations',
            'verifiedReservations',
            'invalidReservations',
            'currentReservations',
            'reservations'
        ));

    }
}
