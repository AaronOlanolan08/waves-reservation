<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Rules\UniqueEmailAcrossTables;

class VendorProfileController extends Controller
{
    public function view_profile()
    {
        $userId = Auth::id();
        $admin = Admin::find($userId);
        return view('admin.vendor.profile.profile', compact('admin'));
    }

    public function edit_profile($id)
    {
        $vendor = Admin::findOrFail($id);
        return view('admin.vendor.profile.edit_profile', compact('vendor'));
    }

    public function update_profile(Request $request, $id)
    {
        $vendor = Admin::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|regex:/^[0-9]{11}$/',
            'email' => ['required', 'string', 'email', 'max:255', new UniqueEmailAcrossTables($vendor->id)],
        ]);

        // Update the customer profile
        $vendor->update($request->only(['name', 'number', 'email']));

        // Flash success message
        return redirect()->route('admin.vendor.profile')->with('success', 'Profile updated successfully!');
    }

    public function showPaymentPage(Reservation $reservation)
    {
        $total = optional($reservation->bill)->grand_total ?? 0;
        return view('admin.vendor.reservations.payment', compact('reservation', 'total'));
    }
}
