<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Amenities;
use App\Models\ReservedAmenity;
use Carbon\Carbon;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AmenitiesController extends Controller
{
    public function view_amenities(Request $request, $type = 'cottage')
    {
        if (!in_array($type, ['cottage', 'table'])) {
            return redirect()->back()->with('error', 'Invalid amenities type.');
        }

        $dateTime = $request->input('date_time', now());
        $currentDateTime = Carbon::parse($dateTime);

        $date = $currentDateTime->toDateString();
        $time = $currentDateTime->format('H:i:s');

        // Correct column name: `amenity_id`
        $reservedAmenities = ReservedAmenity::whereHas('reservation', function ($query) use ($date, $time) {
            $query->whereDate('date', $date)
                ->where('startTime', '<=', $time)
                ->where('endTime', '>=', $time);
        })->pluck('amenity_id')->toArray();

        $amenities = Amenities::where('type', $type)->get();

        foreach ($amenities as $amenity) {
            $amenity->availability_status = ($amenity->is_active && !in_array($amenity->id, $reservedAmenities))
                ? 'Available'
                : 'Not Available';
        }

        if ($request->ajax()) {
            return response()->json(['amenities' => $amenities]);
        }

        $userId = Auth::id();
        $user = Admin::find($userId);

        if ($user->role == 'Manager') {
            return view('admin.manager.amenities.index', compact('amenities', 'type'));
        } elseif ($user->role == 'Vendor') {
            return view('admin.vendor.amenities.index', compact('amenities', 'type'));
        }

        return redirect()->route('login')->with('error', 'Unauthorized access.');
    }

    public function add_amenity(Request $request)
    {
        Log::debug('Incoming request data:', $request->all());

        // Get the type from the request, defaulting to 'cottage' if not provided
        $type = $request->input('type', 'cottage');

        // Ensure that the type is either 'cottage' or 'table'
        if (!in_array($type, ['cottage', 'table'])) {
            return redirect()->back()->with('error', 'Invalid amenity type selected.');
        }

        // Get the authenticated admin's ID
        $userId = Auth::id();

        Log::debug('Admin ID:', [$userId]);

        // Validate the input data, adding the type and added_by to the validation
        $validator = $this->validator(array_merge($request->all(), ['type' => $type, 'added_by' => $userId]));

        // Handle validation failure
        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->all());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create the new amenity (either cottage or table)
        Amenities::create([
            'name' => $request->name,
            'price' => $request->price,
            'type' => $type,
            'added_by' => $userId,
        ]);

        return redirect()->back()->with('success', ucfirst($type) . ' added successfully!');
    }

    public function update_amenity(Request $request, $type, $id)
    {
        $amenity = Amenities::where('type', $type)->findOrFail($id);

        // Validate the data
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        // Update the amenity (whether it's a cottage or table)
        $amenity->update([
            'name' => $request->name,
            'price' => $request->price,
        ]);

        return redirect()->back()->with('success', ucfirst($type) . ' updated successfully!');
    }
    public function archive($id)
    {
        $amenity = Amenities::findOrFail($id);
        $amenity->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Cottage archived successfully!');
    }

    public function unarchive($id)
    {
        $amenity = Amenities::findOrFail($id);
        $amenity->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Cottage unarchived successfully!');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'type' => ['required', 'string', 'max:255'],
            'added_by' => ['required', 'integer'],
        ]);
    }

    public function showCancelledAmenities()
    {
        $today = Carbon::today();

        $cancelledAmenities = ReservedAmenity::with(['amenity', 'reservation.customer'])
            ->where('reactivated', 0) // Only where not reactivated
            ->whereHas('reservation', function ($query) use ($today) {
                $query->where('status', 'cancelled')
                    ->whereDate('date', '>=', $today);
            })
            ->get();

        return view('admin.vendor.amenities.cancel_amen', compact('cancelledAmenities'));
    }

    public function activateAmenity(Request $request)
    {
        $amenityId = $request->input('amenity_id');
        Log::info('Received activate amenity request.', ['amenity_id' => $amenityId]);

        $updated = ReservedAmenity::where('amenity_id', $amenityId)
            ->whereHas('reservation', function ($query) {
                $query->where('status', 'cancelled');
            })
            ->update(['reactivated' => true]);

        Log::info('Update result', ['rows_updated' => $updated]);

        if ($updated > 0) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false], 404);
        }
    }
}
