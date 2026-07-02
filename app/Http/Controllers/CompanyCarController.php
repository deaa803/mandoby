<?php

namespace App\Http\Controllers;

use App\Models\CompanyCar;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;


class CompanyCarController extends Controller
{
    /**
     * Display a listing of company cars.
     */
    public function index(Request $request)
    {
        $company = $request->user()?->company;

        $cars = CompanyCar::with(['company', 'driver.user'])
            ->when($company, fn ($query) => $query->where('company_id', $company->id))
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Company cars retrieved successfully',
            'data' => $cars,
        ]);
    }

    /**
     * Store a newly created company car and create a linked driver account.
     */
    public function store(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company && !$request->filled('company_id')) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'vehicle_type' => ['required', 'string', 'max:255'],
            'driver_name' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:255'],

            'driver_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'driver_password' => ['required', 'string', 'min:8'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $companyId = $company?->id ?? $validated['company_id'];

        try {
            $result = DB::transaction(function () use ($validated, $companyId) {
                $car = CompanyCar::create([
                    'company_id' => $companyId,
                    'vehicle_type' => $validated['vehicle_type'],
                    'driver_name' => $validated['driver_name'],
                    'plate_number' => $validated['plate_number'],
                ]);

                $user = User::create([
                    'name' => $validated['driver_name'],
                    'email' => $validated['driver_email'],
                    'password' => Hash::make($validated['driver_password']),
                    'phone' => $validated['driver_phone'] ?? null,
                    'user_type' => 'driver',
                ]);

                $driver = Driver::create([
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                    'company_car_id' => $car->id,
                    'status' => 'available',
                ]);

                return [
                    'car' => $car->fresh()->load(['company', 'driver.user']),
                    'driver' => $driver->load(['user', 'company', 'car']),
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
                'message' => 'Failed to create company car and driver account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified company car.
     */
    public function show(CompanyCar $companyCar)
    {
        return response()->json([
            'status' => true,
            'message' => 'Company car retrieved successfully',
            'data' => $companyCar->load(['company', 'driver.user']),
        ]);
    }

    /**
     * Update the specified company car.
     */
    public function update(Request $request, CompanyCar $companyCar)
    {
        $validated = $request->validate([
            'company_id' => ['sometimes', 'required', 'exists:companies,id'],
            'vehicle_type' => ['sometimes', 'required', 'string', 'max:255'],
            'driver_name' => ['sometimes', 'required', 'string', 'max:255'],
            'plate_number' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $companyCar->update($validated);

        if (array_key_exists('driver_name', $validated) && $companyCar->driver?->user) {
            $companyCar->driver->user->update([
                'name' => $validated['driver_name'],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Company car updated successfully',
            'data' => $companyCar->fresh()->load(['company', 'driver.user']),
        ]);
    }

    /**
     * Remove the specified company car.
     */
    public function destroy(CompanyCar $companyCar)
    {
        DB::transaction(function () use ($companyCar) {
            foreach ($companyCar->drivers as $driver) {
                $driver->user?->delete();
            }

            $companyCar->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Company car and linked driver account deleted successfully',
        ]);
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

        $cars = CompanyCar::where('company_id', $companyId)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Company cars retrieved successfully',
            'data' => $cars,
        ]);
    }
}
