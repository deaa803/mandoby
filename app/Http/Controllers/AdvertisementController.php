<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Database\Eloquent\Builder;

class AdvertisementController extends Controller
{
    private array $relations = [
        'company',
        'productDetail.product',
        'productDetail.category',
        'productDetail.images',
        'productDetail.features',
        'productDetail.model3d',
    ];

    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Advertisements retrieved successfully',
            'data' => $this->publishedQuery()
                ->latest()
                ->get(),
        ]);
    }

    public function show(Advertisement $advertisement)
    {
        $published = $this->publishedQuery()
            ->whereKey($advertisement->id)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'message' => 'Advertisement retrieved successfully',
            'data' => $published,
        ]);
    }

    private function publishedQuery(): Builder
    {
        $now = now();

        return Advertisement::with($this->relations)
            ->where('status', 'active')
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }
}
