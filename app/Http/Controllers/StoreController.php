<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreController extends Controller
{
    /**
     * Display a listing of stores.
     */
    public function index()
    {
        $stores = Store::with('user')->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Stores retrieved successfully',
            'data' => $stores,
        ], 200);
    }

    /**
     * Store a newly created store.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // user fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // store fields
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
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'user_type' => 'store',
                ]);

                $store = Store::create([
                    'user_id' => $user->id,
                    'name_store' => $validated['name_store'],
                    'activity_type' => $validated['activity_type'],
                ]);

                $token = $user->createToken($user->name)->plainTextToken;

                return [
                    'store' => $store->load('user'),
                    'token' => $token,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Store created successfully',
                'data' => $result['store'],
                'token' => $result['token'],
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create store',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified store.
     */
    public function show(Store $store)
    {
        return response()->json([
            'status' => true,
            'message' => 'Store retrieved successfully',
            'data' => $store->load(['user', 'companies', 'orders']),
        ], 200);
    }

    /**
     * Update the specified store.
     */
    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            // user fields
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $store->user_id],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],

            // store fields
            'name_store' => ['sometimes', 'string', 'max:255'],
            'activity_type' => ['sometimes', 'string', 'max:255'],
        ]);

        try {
            $updatedStore = DB::transaction(function () use ($validated, $store) {
                $userData = collect($validated)
                    ->only(['name', 'email', 'phone', 'latitude', 'longitude'])
                    ->toArray();

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                if (!empty($userData)) {
                    $store->user->update($userData);
                }

                $storeData = collect($validated)
                    ->only(['name_store', 'activity_type'])
                    ->toArray();

                if (!empty($storeData)) {
                    $store->update($storeData);
                }

                return $store->fresh()->load('user');
            });

            return response()->json([
                'status' => true,
                'message' => 'Store updated successfully',
                'data' => $updatedStore,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update store',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified store.
     */
    public function destroy(Store $store)
    {
        try {
            DB::transaction(function () use ($store) {
                $store->user->delete();
            });

            return response()->json([
                'status' => true,
                'message' => 'Store deleted successfully',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete store',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
