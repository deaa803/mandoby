<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function logindriver()
    {
        $validated = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        $user = Auth::user();

        if ($user->user_type !== 'driver') {
            Auth::logout();

            return response()->json([
                'status' => false,
                'message' => 'This account is not a driver account',
            ], 403);
        }

        $user->load('driver.company');

        if (!$user->driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver profile not found',
            ], 404);
        }

        $token = $user->createToken('driver-app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Driver login successfully',
            'data' => [
                'token' => $token,
                'driver' => [
                    'id' => $user->driver->id,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'company_id' => $user->driver->company_id,
                    'company_name' => $user->driver->company?->name_company,
                    'vehicle_type' => $user->driver->vehicle_type,
                    'plate_number' => $user->driver->plate_number,
                    'is_active' => $user->driver->is_active,
                ],
            ],
        ]);
    }
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
                'data' => null,
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->currentAccessToken()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
                'data' => null,
            ], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
            'data' => null,
        ]);
    }
}
