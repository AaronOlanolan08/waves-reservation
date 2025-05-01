<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\DownPayment;
use App\Models\Reservation;

class AdminDashboardController extends Controller
{
    // public function __construct()
    // {
    //     // Ensures that only authenticated admins can access this controller
    //     $this->middleware('auth:admin');
    // }

    public function create()
    {
        return view('admin.dashboard');
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

        return redirect()->route('admin.create.account');
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
