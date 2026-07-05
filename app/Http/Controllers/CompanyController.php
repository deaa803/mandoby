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
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Companies retrieved successfully',
            'data' => Company::with('user')->latest()->get(),
        ]);
    }


    /**
     * Safe company catalogue for store accounts.
     */
    public function browse()
    {
        return response()->json([
            'status' => true,
            'message' => 'Companies retrieved successfully',
            'data' => Company::query()
                ->select([
                    'id',
                    'name_company',
                    'description',
                    'logo',
                    'has_3d_access',
                    'model_3d_expires_at',
                    'created_at',
                    'updated_at',
                ])
                ->latest()
                ->get(),
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
                    'address' => $validated['address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'user_type' => 'company',
                ]);

                $logo = $request->hasFile('logo')
                    ? $request->file('logo')->store('companies/logos', 'public')
                    : null;

                $company = Company::create([
                    'user_id' => $user->id,
                    'name_company' => $validated['name_company'],
                    'description' => $validated['description'],
                    'logo' => $logo,
                ]);

                return [
                    'user' => $user->load('company'),
                    'company' => $company->load('user'),
                    'token' => $user->createToken($user->name)->plainTextToken,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Company created successfully',
                'data' => [
                    'user' => $result['user'],
                    'company' => $result['company'],
                    'token' => $result['token'],
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Company $company)
    {
        return response()->json([
            'status' => true,
            'message' => 'Company retrieved successfully',
            'data' => $company->load(['user', 'cars', 'productDetails', 'stores']),
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $company->user_id],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address' => ['sometimes', 'string', 'max:500'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'name_company' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        try {
            $updated = DB::transaction(function () use ($request, $validated, $company) {
                $userData = collect($validated)
                    ->only(['name', 'email', 'phone', 'address', 'latitude', 'longitude'])
                    ->toArray();

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                if ($userData !== []) {
                    $company->user->update($userData);
                }

                $companyData = collect($validated)
                    ->only(['name_company', 'description'])
                    ->toArray();

                if ($request->hasFile('logo')) {
                    if ($company->logo) {
                        Storage::disk('public')->delete($company->logo);
                    }

                    $companyData['logo'] = $request->file('logo')
                        ->store('companies/logos', 'public');
                }

                if ($companyData !== []) {
                    $company->update($companyData);
                }

                return $company->fresh()->load('user');
            });

            return response()->json([
                'status' => true,
                'message' => 'Company updated successfully',
                'data' => $updated,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update the authenticated company's logo without exposing another
     * company's resource identifier to the mobile application.
     */
    public function updateOwnLogo(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        try {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }

            $company->update([
                'logo' => $request->file('logo')->store('companies/logos', 'public'),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Company logo updated successfully',
                'data' => $company->fresh()->load('user'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update company logo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Company $company)
    {
        try {
            $company->user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Company deleted successfully',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
