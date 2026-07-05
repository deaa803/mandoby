<?php

namespace App\Http\Controllers;

use App\Models\CompanyCar;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::with(['user', 'car.company'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Drivers retrieved successfully',
            'data' => $drivers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'company_car_id' => [
                'required',
                'exists:company_cars,id',
                Rule::unique('drivers', 'company_car_id'),
            ],
            'status' => ['nullable', 'in:available,busy,offline'],
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                $car = CompanyCar::with('company.user')
                    ->findOrFail($validated['company_car_id']);
                $companyUser = $car->company?->user;

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address']
                        ?? $companyUser?->address
                        ?? 'عنوان غير محدد',
                    'latitude' => $validated['latitude']
                        ?? $companyUser?->latitude,
                    'longitude' => $validated['longitude']
                        ?? $companyUser?->longitude,
                    'user_type' => 'driver',
                ]);

                $driver = Driver::create([
                    'user_id' => $user->id,
                    'company_car_id' => $validated['company_car_id'],
                    'status' => $validated['status'] ?? 'available',
                ]);

                return [
                    'driver' => $driver->load(['user', 'car.company']),
                    'token' => $user->createToken($user->name)->plainTextToken,
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

    public function show(Driver $driver)
    {
        return response()->json([
            'status' => true,
            'message' => 'Driver retrieved successfully',
            'data' => $driver->load(['user', 'car.company', 'orders']),
        ]);
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($driver->user_id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address' => ['sometimes', 'required', 'string', 'max:500'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'company_car_id' => [
                'sometimes',
                'required',
                'exists:company_cars,id',
                Rule::unique('drivers', 'company_car_id')->ignore($driver->id),
            ],
            'status' => ['sometimes', 'in:available,busy,offline'],
        ]);

        try {
            $updatedDriver = DB::transaction(function () use ($validated, $driver) {
                $userData = collect($validated)
                    ->only(['name', 'email', 'phone', 'address', 'latitude', 'longitude'])
                    ->toArray();

                if (! empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                if ($userData !== []) {
                    $driver->user->update($userData);
                }

                $driverData = collect($validated)
                    ->only(['company_car_id', 'status'])
                    ->toArray();

                if ($driverData !== []) {
                    $driver->update($driverData);
                }

                return $driver->fresh()->load(['user', 'car.company']);
            });

            return response()->json([
                'status' => true,
                'message' => 'Driver updated successfully',
                'data' => $updatedDriver,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update driver',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Driver $driver)
    {
        try {
            DB::transaction(function () use ($driver) {
                if ($driver->user) {
                    $driver->user->delete();
                } else {
                    $driver->delete();
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Driver deleted successfully',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete driver',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'current_lat' => ['required', 'numeric', 'between:-90,90'],
            'current_lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $user = $request->user()->loadMissing('driver');

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
            'data' => $user->driver->fresh()->load(['user', 'car.company']),
        ]);
    }
}
