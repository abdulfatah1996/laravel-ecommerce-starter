<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'description', 'price', 'compare_at_price',
        'stock', 'images', 'is_active', 'is_featured', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'stock' => 'integer',
            'images' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /** @return array<int,string> روابط الصور */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->filter()
            ->map(fn (string $path) => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->imageUrls()[0] ?? null;
    }

    public function hasDiscount(): bool
    {
        return $this->compare_at_price !== null
            && (float) $this->compare_at_price > (float) $this->price;
    }
}
