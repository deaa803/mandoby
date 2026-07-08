<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:500'],
            'platform' => ['nullable', 'in:android,ios,web,unknown'],
            'app_type' => ['required', 'in:driver,company,customer'],
        ]);

        DeviceToken::updateOrCreate(
            [
                'fcm_token' => $data['fcm_token'],
            ],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'] ?? 'unknown',
                'app_type' => $data['app_type'],
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Device token saved successfully.',
        ]);
    }
}
