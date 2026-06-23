<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function index()
    {
        $images = Image::with('productDetail')->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Images retrieved successfully',
            'data' => $images,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_detail_id' => ['required', 'exists:product_details,id'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $path = $request->file('image')->store('product-details/images', 'public');

        $image = Image::create([
            'product_detail_id' => $validated['product_detail_id'],
            'url' => $path,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Image uploaded successfully',
            'data' => $image->load('productDetail'),
        ], 201);
    }

    public function show(Image $image)
    {
        return response()->json([
            'status' => true,
            'message' => 'Image retrieved successfully',
            'data' => $image->load('productDetail'),
        ]);
    }

    public function update(Request $request, Image $image)
    {
        $validated = $request->validate([
            'product_detail_id' => ['sometimes', 'required', 'exists:product_details,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = [];

        if (isset($validated['product_detail_id'])) {
            $data['product_detail_id'] = $validated['product_detail_id'];
        }

        if ($request->hasFile('image')) {
            if ($image->url) {
                Storage::disk('public')->delete($image->url);
            }

            $data['url'] = $request->file('image')->store('product-details/images', 'public');
        }

        if (!empty($data)) {
            $image->update($data);
        }

        return response()->json([
            'status' => true,
            'message' => 'Image updated successfully',
            'data' => $image->fresh()->load('productDetail'),
        ]);
    }

    public function destroy(Image $image)
    {
        if ($image->url) {
            Storage::disk('public')->delete($image->url);
        }

        $image->delete();

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully',
            'data' => null,
        ]);
    }
}
