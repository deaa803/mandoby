<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    public function index()
    {
        $advertisements = Advertisement::with([
            'company',
            'productDetail.product',
            'productDetail.images',
        ])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Advertisements retrieved successfully',
            'data' => $advertisements,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'product_detail_id' => ['nullable', 'exists:product_details,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,active,rejected,expired'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $path = $request->file('image')->store('advertisements', 'public');

        $advertisement = Advertisement::create([
            'company_id' => $validated['company_id'],
            'product_detail_id' => $validated['product_detail_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image' => $path,
            'price' => $validated['price'] ?? 0,
            'status' => $validated['status'] ?? 'active',
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Advertisement created successfully',
            'data' => $advertisement->load([
                'company',
                'productDetail.product',
                'productDetail.images',
            ]),
        ], 201);
    }

    public function show(Advertisement $advertisement)
    {
        return response()->json([
            'status' => true,
            'message' => 'Advertisement retrieved successfully',
            'data' => $advertisement->load([
                'company',
                'productDetail.product',
                'productDetail.images',
            ]),
        ]);
    }

    public function update(Request $request, Advertisement $advertisement)
    {
        $validated = $request->validate([
            'company_id' => ['sometimes', 'required', 'exists:companies,id'],
            'product_detail_id' => ['nullable', 'exists:product_details,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,active,rejected,expired'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        if ($request->hasFile('image')) {
            if ($advertisement->image) {
                Storage::disk('public')->delete($advertisement->image);
            }

            $validated['image'] = $request->file('image')->store('advertisements', 'public');
        }

        $advertisement->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Advertisement updated successfully',
            'data' => $advertisement->fresh()->load([
                'company',
                'productDetail.product',
                'productDetail.images',
            ]),
        ]);
    }

    public function destroy(Advertisement $advertisement)
    {
        if ($advertisement->image) {
            Storage::disk('public')->delete($advertisement->image);
        }

        $advertisement->delete();

        return response()->json([
            'status' => true,
            'message' => 'Advertisement deleted successfully',
            'data' => null,
        ]);
    }
}
