<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function storeCompany(Request $request)
    {
        return $this->save($request, 'company');
    }

    public function storeStore(Request $request)
    {
        return $this->save($request, 'store');
    }

    private function save(Request $request, ?string $forcedAppType = null)
    {
        $rules = [
            'fcm_token' => ['required', 'string', 'max:500'],
            'platform' => ['nullable', Rule::in(['android', 'ios', 'web', 'unknown'])],
        ];

        if (!$forcedAppType) {
            $rules['app_type'] = ['required', Rule::in(['driver', 'company', 'store', 'customer'])];
        }

        $data = $request->validate($rules);
        $appType = $forcedAppType ?: $data['app_type'];

        $token = DeviceToken::updateOrCreate(
            [
                'fcm_token' => $data['fcm_token'],
            ],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'] ?? 'unknown',
                'app_type' => $appType,
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Device token saved successfully.',
            'data' => [
                'id' => $token->id,
                'app_type' => $token->app_type,
                'last_seen_at' => optional($token->last_seen_at)->toDateTimeString(),
            ],
        ]);
    }
}
