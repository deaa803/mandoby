<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    /**
     * Display a listing of drivers.
     */
    public function index()
    {
        $drivers = Driver::with(['user', 'company', 'car'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Drivers retrieved successfully',
            'data' => $drivers,
        ], 200);
    }

    /**
     * Store a newly created driver.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // user fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],

            // driver fields
            'company_id' => ['required', 'exists:companies,id'],
            'company_car_id' => ['nullable', 'exists:company_cars,id'],
            'status' => ['nullable', 'in:available,busy,offline'],
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'user_type' => 'driver',
                ]);

                $driver = Driver::create([
                    'user_id' => $user->id,
                    'company_id' => $validated['company_id'],
                    'company_car_id' => $validated['company_car_id'] ?? null,
                    'status' => $validated['status'] ?? 'available',
                ]);

                $token = $user->createToken($user->name)->plainTextToken;

                return [
                    'driver' => $driver->load(['user', 'company', 'car']),
                    'token' => $token,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Driver created successfully',
                'data' => $result['driver'],
                'token' => $result['token'],
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create driver',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified driver.
     */
    public function show(Driver $driver)
    {
        return response()->json([
            'status' => true,
            'message' => 'Driver retrieved successfully',
            'data' => $driver->load(['user', 'company', 'car', 'orders']),
        ], 200);
    }

    /**
     * Update the specified driver.
     */
    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            // user fields
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $driver->user_id],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],

            // driver fields
            'company_id' => ['sometimes', 'exists:companies,id'],
            'company_car_id' => ['sometimes', 'nullable', 'exists:company_cars,id'],
            'status' => ['sometimes', 'in:available,busy,offline'],
        ]);

        try {
            $updatedDriver = DB::transaction(function () use ($validated, $driver) {
                $userData = collect($validated)
                    ->only(['name', 'email', 'phone', 'address'])
                    ->toArray();

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                if (!empty($userData)) {
                    $driver->user->update($userData);
                }

                $driverData = collect($validated)
                    ->only(['company_id', 'company_car_id', 'status'])
                    ->toArray();

                if (!empty($driverData)) {
                    $driver->update($driverData);
                }

                return $driver->fresh()->load(['user', 'company', 'car']);
            });

            return response()->json([
                'status' => true,
                'message' => 'Driver updated successfully',
                'data' => $updatedDriver,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update driver',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified driver.
     */
    public function destroy(Driver $driver)
    {
        try {
            DB::transaction(function () use ($driver) {
                $driver->user->delete();
            });

            return response()->json([
                'status' => true,
                'message' => 'Driver deleted successfully',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete driver',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update current driver's location.
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'current_lat' => ['required', 'numeric', 'between:-90,90'],
            'current_lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $user = $request->user();

        if ($user->user_type !== 'driver' || ! $user->driver) {
            return response()->json([
                'status' => false,
                'message' => 'Only drivers can update location',
            ], 403);
        }

        $user->driver->update([
            'current_lat' => $validated['current_lat'],
            'current_lng' => $validated['current_lng'],
            'last_location_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Driver location updated successfully',
            'data' => $user->driver->fresh()->load(['user', 'company', 'car']),
        ], 200);
    }


    /**
     * Get current driver's assigned orders.
     */
    public function myOrders(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'driver' || ! $user->driver) {
            return response()->json([
                'status' => false,
                'message' => 'Only drivers can view driver orders',
            ], 403);
        }

        $orders = $user->driver->orders()
            ->with(['store', 'productDetails', 'payments'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Driver orders retrieved successfully',
            'data' => $orders,
        ], 200);
    }
}
