<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'label', 'sku', 'price', 'stock', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'stock' => 'integer', 'is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** سعر الخيار، أو سعر المنتج إن لم يُحدَّد. */
    public function effectivePrice(): float
    {
        return (float) ($this->price ?? $this->product->price);
    }
}
