<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverFcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'fcm_token' => ['required', 'string'],
        ]);

        $driver = Driver::findOrFail($validated['driver_id']);

        $driver->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'FCM token saved successfully',
            'data' => [
                'driver_id' => $driver->id,
            ],
        ]);
    }
}
