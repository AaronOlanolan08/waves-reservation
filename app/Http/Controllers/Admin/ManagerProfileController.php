<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManagerProfileController extends Controller
{

    public function view_all_reservations()
    {
        return view('admin.manager.reservations.all_reservations');
    }

    public function view_profile()
    {
        $userId = Auth::id();
        $admin = Admin::find($userId);
        return view('admin.manager.profile.profile', compact('admin'));
    }

    public function view_vendors_list()
    {
        $vendors = Admin::whereRaw('LOWER(role) = ?', ['vendor'])->get();

        return view('admin.manager.profile.vendors_list', compact('vendors'));
    }

    public function update_profile(Request $request, $id)
    {
        // Debug: Log start of update process
        Log::info('🛠️ Manager Profile Update: STARTED');

        // Get the currently authenticated admin (modify as needed)
        $admin = Admin::findOrFail($id);

        // Debug: Log current admin data before update
        Log::debug('👤 Current Admin Data:', $admin->toArray());

        Log::debug('📥 Incoming Request Data:', $request->all());
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|regex:/^[0-9]{11}$/',
            'email' => 'required|string|email|max:255',
        ]);

        // Debug: Log validated data
        Log::info('✅ Validated Data:', $validated);

        // Update the admin record
        $admin->update($validated);

        // Debug: Log updated admin data
        Log::info('✅ Admin Updated Successfully:', $admin->toArray());

        // Debug: Log end of update process
        Log::info('🏁 Manager Profile Update: COMPLETED');

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
    public function update_vendors_list(Request $request, $id)
    {
        $vendor = Admin::find($request->id);
        if ($vendor) {
            $vendor->role = $request->role;
            $vendor->save();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function view_del_req()
    {
        return view('admin.manager.profile.del_req');
    }
}
