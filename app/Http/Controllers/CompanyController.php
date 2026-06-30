<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index()
    {
        $companies = Company::with('user')->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Companies retrieved successfully',
            'data' => $companies,
        ], 200);
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // user fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // company fields
            'name_company' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        try {
            $result = DB::transaction(function () use ($request, $validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? null,
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'user_type' => 'company',
                ]);

                $logoPath = null;

                if ($request->hasFile('logo')) {
                    $logoPath = $request->file('logo')->store('companies/logos', 'public');
                }

                $company = Company::create([
                    'user_id' => $user->id,
                    'name_company' => $validated['name_company'],
                    'description' => $validated['description'],
                    'logo' => $logoPath,
                ]);

                $token = $user->createToken($user->name)->plainTextToken;

                return [
                    'company' => $company->load('user'),
                    'token' => $token,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Company created successfully',
                'data' => $result['company'],
                'token' => $result['token'],
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company)
    {
        return response()->json([
            'status' => true,
            'message' => 'Company retrieved successfully',
            'data' => $company->load(['user', 'cars', 'productDetails', 'stores']),
        ], 200);
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            // user fields
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $company->user_id],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],

            // company fields
            'name_company' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        try {
            $updatedCompany = DB::transaction(function () use ($request, $validated, $company) {
                $userData = collect($validated)
                    ->only(['name', 'email', 'phone', 'latitude', 'longitude'])
                    ->toArray();

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                if (!empty($userData)) {
                    $company->user->update($userData);
                }

                $companyData = collect($validated)
                    ->only(['name_company', 'description'])
                    ->toArray();

                if ($request->hasFile('logo')) {
                    if ($company->logo) {
                        Storage::disk('public')->delete($company->logo);
                    }

                    $companyData['logo'] = $request->file('logo')->store('companies/logos', 'public');
                }

                if (!empty($companyData)) {
                    $company->update($companyData);
                }

                return $company->fresh()->load('user');
            });

            return response()->json([
                'status' => true,
                'message' => 'Company updated successfully',
                'data' => $updatedCompany,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Company $company)
    {
        try {
            DB::transaction(function () use ($company) {
                if ($company->logo) {
                    Storage::disk('public')->delete($company->logo);
                }

                $company->user->delete();
            });

            return response()->json([
                'status' => true,
                'message' => 'Company deleted successfully',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
