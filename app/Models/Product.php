<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'description', 'price', 'compare_at_price',
        'stock', 'images', 'option_name', 'delivery_note', 'preview_3d', 'is_active', 'is_featured', 'is_demo',
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

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeVariants()
    {
        return $this->variants()->where('is_active', true);
    }

    public function hasVariants(): bool
    {
        return $this->relationLoaded('variants')
            ? $this->variants->where('is_active', true)->isNotEmpty()
            : $this->activeVariants()->exists();
    }

    /** المخزون الكلي: مجموع الخيارات الفعّالة إن وُجدت وإلا مخزون المنتج. */
    public function totalStock(): int
    {
        if ($this->hasVariants()) {
            return (int) $this->variants->where('is_active', true)->sum('stock');
        }

        return (int) $this->stock;
    }

    /** أقل سعر ممكن (لعرض «ابتداءً من» في البطاقات). */
    public function minPrice(): float
    {
        if ($this->hasVariants()) {
            return (float) $this->variants->where('is_active', true)
                ->map(fn (ProductVariant $v) => $v->price !== null ? (float) $v->price : (float) $this->price)->min();
        }

        return (float) $this->price;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(fn ($p) => $p->where('stock', '>', 0)->whereDoesntHave('variants', fn ($v) => $v->where('is_active', true)))
                ->orWhereHas('variants', fn ($v) => $v->where('is_active', true)->where('stock', '>', 0));
        });
    }

    public function isInStock(): bool
    {
        return $this->totalStock() > 0;
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
