<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Stores retrieved successfully',
            'data' => Store::with('user')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'name_store' => ['required', 'string', 'max:255'],
            'activity_type' => ['required', 'string', 'max:255'],
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'user_type' => 'store',
                ]);

                $store = Store::create([
                    'user_id' => $user->id,
                    'name_store' => $validated['name_store'],
                    'activity_type' => $validated['activity_type'],
                ]);

                return [
                    'user' => $user->load('store'),
                    'store' => $store->load('user'),
                    'token' => $user->createToken($user->name)->plainTextToken,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Store created successfully',
                'data' => [
                    'user' => $result['user'],
                    'store' => $result['store'],
                    'token' => $result['token'],
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create store',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Store $store)
    {
        return response()->json([
            'status' => true,
            'message' => 'Store retrieved successfully',
            'data' => $store->load(['user', 'companies', 'orders']),
        ]);
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $store->user_id],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address' => ['sometimes', 'string', 'max:500'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'name_store' => ['sometimes', 'string', 'max:255'],
            'activity_type' => ['sometimes', 'string', 'max:255'],
        ]);

        try {
            $updated = DB::transaction(function () use ($validated, $store) {
                $userData = collect($validated)
                    ->only(['name', 'email', 'phone', 'address', 'latitude', 'longitude'])
                    ->toArray();

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                if ($userData !== []) {
                    $store->user->update($userData);
                }

                $storeData = collect($validated)
                    ->only(['name_store', 'activity_type'])
                    ->toArray();

                if ($storeData !== []) {
                    $store->update($storeData);
                }

                return $store->fresh()->load('user');
            });

            return response()->json([
                'status' => true,
                'message' => 'Store updated successfully',
                'data' => $updated,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update store',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Store $store)
    {
        try {
            $store->user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Store deleted successfully',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete store',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
