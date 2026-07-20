<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyCar;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CompanyCarController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $cars = CompanyCar::with(['company', 'driver.user'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Company cars retrieved successfully',
            'data' => $cars,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'vehicle_type' => ['required', 'string', 'max:255'],
            'max_load_kg' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'plate_number' => [
                'required',
                'string',
                'max:255',
                'unique:company_cars,plate_number',
            ],

            'driver_name' => ['required', 'string', 'max:255'],
            'driver_email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'driver_password' => ['required', 'string', 'min:8'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
        ]);

        try {
            $result = DB::transaction(function () use ($validated, $company) {
                $car = CompanyCar::create([
                    'company_id' => $company->id,
                    'vehicle_type' => $validated['vehicle_type'],
                    'plate_number' => $validated['plate_number'],
                    'max_load_kg' => $validated['max_load_kg'],
                ]);

                $user = User::create([
                    'name' => $validated['driver_name'],
                    'email' => $validated['driver_email'],
                    'password' => Hash::make($validated['driver_password']),
                    'phone' => $validated['driver_phone'] ?? null,
                    'address' => $company->user?->address ?? 'غير محدد',
                    'latitude' => $company->user?->latitude,
                    'longitude' => $company->user?->longitude,
                    'user_type' => 'driver',
                ]);

                $driver = Driver::create([
                    'user_id' => $user->id,
                    'company_car_id' => $car->id,
                    'status' => 'available',
                ]);

                return [
                    'car' => $car->fresh()->load(['company', 'driver.user']),
                    'driver' => $driver->load(['user', 'car.company']),
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Company car and driver account created successfully',
                'data' => $result,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create company car',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, CompanyCar $companyCar)
    {
        if (!$this->ownsCar($request, $companyCar)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to access this car',
                'data' => null,
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Company car retrieved successfully',
            'data' => $companyCar->load(['company', 'driver.user']),
        ]);
    }

    public function update(Request $request, CompanyCar $companyCar)
    {
        if (!$this->ownsCar($request, $companyCar)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to update this car',
                'data' => null,
            ], 403);
        }

        $validated = $request->validate([
            'vehicle_type' => ['sometimes', 'required', 'string', 'max:255'],
            'plate_number' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('company_cars', 'plate_number')->ignore($companyCar->id),
            ],
            'max_load_kg' => ['sometimes', 'required', 'numeric', 'gt:0', 'max:100000'],
            'driver_name' => ['sometimes', 'required', 'string', 'max:255'],
            'driver_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($validated, $companyCar) {
            $carData = collect($validated)
                ->only(['vehicle_type', 'plate_number', 'max_load_kg'])
                ->toArray();

            if ($carData !== []) {
                $companyCar->update($carData);
            }

            $userData = [];

            if (array_key_exists('driver_name', $validated)) {
                $userData['name'] = $validated['driver_name'];
            }

            if (array_key_exists('driver_phone', $validated)) {
                $userData['phone'] = $validated['driver_phone'];
            }

            if ($userData !== [] && $companyCar->driver?->user) {
                $companyCar->driver->user->update($userData);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Company car updated successfully',
            'data' => $companyCar->fresh()->load(['company', 'driver.user']),
        ]);
    }

    public function destroy(Request $request, CompanyCar $companyCar)
    {
        if (!$this->ownsCar($request, $companyCar)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to delete this car',
                'data' => null,
            ], 403);
        }

        try {
            $companyCar->delete();

            return response()->json([
                'status' => true,
                'message' => 'Company car and linked driver account deleted successfully',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete company car',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function carsByCompany($companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Company cars retrieved successfully',
            'data' => CompanyCar::with('driver.user')
                ->where('company_id', $companyId)
                ->latest()
                ->get(),
        ]);
    }

    private function ownsCar(Request $request, CompanyCar $companyCar): bool
    {
        $company = $request->user()?->company;

        return $company && (int) $companyCar->company_id === (int) $company->id;
    }
}
