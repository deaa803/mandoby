<?php

namespace App\Http\Controllers;

use App\Models\CompanyCar;
use Illuminate\Http\Request;

class CompanyCarController extends Controller
{
    /**
     * Display a listing of company cars.
     */
    public function index()
    {
        $cars = CompanyCar::with('company')->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Company cars retrieved successfully',
            'data' => $cars,
        ]);
    }

    /**
     * Store a newly created company car.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'vehicle_type' => ['required', 'string', 'max:255'],
            'driver_name' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:255'],
        ]);

        $car = CompanyCar::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Company car created successfully',
            'data' => $car->load('company'),
        ], 201);
    }

    /**
     * Display the specified company car.
     */
    public function show(CompanyCar $companyCar)
    {
        return response()->json([
            'status' => true,
            'message' => 'Company car retrieved successfully',
            'data' => $companyCar->load('company'),
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

        return response()->json([
            'status' => true,
            'message' => 'Company car updated successfully',
            'data' => $companyCar->fresh()->load('company'),
        ]);
    }

    /**
     * Remove the specified company car.
     */
    public function destroy(CompanyCar $companyCar)
    {
        $companyCar->delete();

        return response()->json([
            'status' => true,
            'message' => 'Company car deleted successfully',
        ]);
    }
}
