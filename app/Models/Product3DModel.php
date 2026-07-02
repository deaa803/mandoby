<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product3DModel extends Model
{
    use HasFactory;

    protected $table = 'product_3d_models';

    protected $fillable = [
        'product_detail_id',
        'company_id',
        'source_image',
        'model_file',
        'thumbnail',
        'status',
        'progress',
        'error_message',
        'metadata',
        'generated_at',
    ];

    protected $appends = [
        'source_image_url',
        'model_file_url',
        'thumbnail_url',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'metadata' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Product3DModel $model) {
            foreach ([$model->source_image, $model->model_file, $model->thumbnail] as $path) {
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getSourceImageUrlAttribute(): ?string
    {
        return $this->source_image
            ? Storage::disk('public')->url($this->source_image)
            : null;
    }

    public function getModelFileUrlAttribute(): ?string
    {
        return $this->model_file
            ? Storage::disk('public')->url($this->model_file)
            : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? Storage::disk('public')->url($this->thumbnail)
            : null;
    }
}
