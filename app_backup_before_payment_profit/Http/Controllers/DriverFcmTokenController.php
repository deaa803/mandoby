<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverFcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $driver = Driver::findOrFail($validated['driver_id']);

        DB::transaction(function () use ($validated, $driver) {
            Driver::where('fcm_token', $validated['fcm_token'])
                ->where('id', '!=', $driver->id)
                ->update(['fcm_token' => null]);

            $driver->update([
                'fcm_token' => $validated['fcm_token'],
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'FCM token saved successfully',
            'data' => [
                'driver_id' => $driver->id,
            ],
        ]);
    }
}
