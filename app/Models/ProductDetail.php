<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductDetail extends Model
{
    use HasFactory;

    protected $appends = [
        'has_3d_model',
        'has_discount',
    ];

    protected $fillable = [
        'product_id',
        'company_id',
        'category_id',
        'status',
        'price',
        'min_order_quantity',
        'package_weight_kg',
    ];


    protected $casts = [
        'price' => 'decimal:2',
        'min_order_quantity' => 'integer',
        'package_weight_kg' => 'decimal:3',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }



    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product_detail')
            ->withPivot('discount', 'price', 'quantity', 'package_weight_kg', 'line_weight_kg')
            ->withTimestamps();
    }
    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_product_details')
            ->withPivot('value')
            ->withTimestamps();
    }
    public function images()
    {
        return $this->hasMany(Image::class);
    }
    public function advertisement()
    {
        return $this->hasMany(Advertisement::class);
    }

    public function model3d()
    {
        return $this->hasOne(Product3DModel::class);
    }

    public function discount()
    {
        return $this->hasOne(ProductDiscount::class);
    }

    public function getHasDiscountAttribute(): bool
    {
        if ($this->relationLoaded('discount')) {
            return $this->getRelation('discount') !== null;
        }

        return $this->discount()->exists();
    }

    public function discountPercentageForQuantity(int $quantity): float
    {
        $discount = $this->relationLoaded('discount')
            ? $this->getRelation('discount')
            : $this->discount()->first();

        if (!$discount || !$discount->appliesToQuantity($quantity)) {
            return 0;
        }

        return (float) $discount->discount_percentage;
    }

    public function getHas3dModelAttribute(): bool
    {
        if ($this->relationLoaded('model3d')) {
            $model = $this->getRelation('model3d');

            return $model !== null
                && $model->status === 'completed'
                && !empty($model->model_file);
        }

        return $this->model3d()
            ->where('status', 'completed')
            ->whereNotNull('model_file')
            ->exists();
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        if (array_key_exists('model3d', $data)) {
            $data['model_3d'] = $data['model3d'];
            unset($data['model3d']);
        }

        return $data;
    }

}
