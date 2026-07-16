<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    /**
     * Display a listing of features.
     */
    public function index()
    {
        $features = Feature::latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Features retrieved successfully',
            'data' => $features,
        ]);
    }

    /**
     * Store a newly created feature.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:features,name'],
        ]);

        $feature = Feature::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Feature created successfully',
            'data' => $feature,
        ], 201);
    }

    /**
     * Display the specified feature.
     */
    public function show(Feature $feature)
    {
        return response()->json([
            'status' => true,
            'message' => 'Feature retrieved successfully',
            'data' => $feature,
        ]);
    }

    /**
     * Update the specified feature.
     */
    public function update(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:features,name,' . $feature->id],
        ]);

        $feature->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Feature updated successfully',
            'data' => $feature->fresh(),
        ]);
    }

    /**
     * Remove the specified feature.
     */
    public function destroy(Feature $feature)
    {
        $feature->delete();

        return response()->json([
            'status' => true,
            'message' => 'Feature deleted successfully',
        ]);
    }
}
