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

    public function store(Request $request)
    {
        $company = $request->user()?->company;

        if (! $company && ! $request->filled('company_id')) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'vehicle_type' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:255', 'unique:company_cars,plate_number'],
            'driver_name' => ['required', 'string', 'max:255'],
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
                'message' => 'Failed to create company car and driver account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(CompanyCar $companyCar)
    {
        return response()->json([
            'status' => true,
            'message' => 'Company car retrieved successfully',
            'data' => $companyCar->load(['company', 'driver.user']),
        ]);
    }

    public function update(Request $request, CompanyCar $companyCar)
    {
        $validated = $request->validate([
            'company_id' => ['sometimes', 'required', 'exists:companies,id'],
            'vehicle_type' => ['sometimes', 'required', 'string', 'max:255'],
            'plate_number' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('company_cars', 'plate_number')->ignore($companyCar->id),
            ],
            'driver_name' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $companyCar) {
            $carData = collect($validated)
                ->only(['company_id', 'vehicle_type', 'plate_number'])
                ->toArray();

            if ($carData !== []) {
                $companyCar->update($carData);
            }

            if (isset($validated['driver_name']) && $companyCar->driver?->user) {
                $companyCar->driver->user->update([
                    'name' => $validated['driver_name'],
                ]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Company car updated successfully',
            'data' => $companyCar->fresh()->load(['company', 'driver.user']),
        ]);
    }

    public function destroy(CompanyCar $companyCar)
    {
        try {
            DB::transaction(function () use ($companyCar) {
                $companyCar->delete();
            });

            return response()->json([
                'status' => true,
                'message' => 'Company car and linked driver account deleted successfully',
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

        if (! $company) {
            return response()->json([
                'status' => false,
                'message' => 'Company not found',
                'data' => null,
            ], 404);
        }

        $cars = CompanyCar::with('driver.user')
            ->where('company_id', $companyId)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Company cars retrieved successfully',
            'data' => $cars,
        ]);
    }
}
