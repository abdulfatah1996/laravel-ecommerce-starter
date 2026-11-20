<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:190'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'availability' => ['nullable', 'in:in_stock'],
            'collection' => ['nullable', 'in:featured'],
            'sort' => ['nullable', 'in:latest,price_asc,price_desc,name'],
        ]);

        $query = Product::query()->active()->with(['category', 'variants']);

        if (($filters['collection'] ?? null) === 'featured') {
            $query->where('is_featured', true);
        }

        if (filled($filters['q'] ?? null)) {
            // نهرب محارف LIKE الخاصة كي لا يتحول البحث إلى نمط واسع.
            $term = addcslashes($filters['q'], '%_\\');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%"));
        }

        if (filled($filters['category'] ?? null)) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category'])->where('is_active', true));
        }

        // السعر الفعلي: أقل سعر بين المنتج وخياراته الفعّالة.
        $effective = "COALESCE((SELECT MIN(COALESCE(pv.price, products.price)) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.price)";

        if (filled($filters['min_price'] ?? null)) {
            $query->whereRaw("{$effective} >= ".$this->number($filters['min_price']));
        }

        if (filled($filters['max_price'] ?? null)) {
            $query->whereRaw("{$effective} <= ".$this->number($filters['max_price']));
        }

        if (($filters['availability'] ?? null) === 'in_stock') {
            $query->inStock();
        }

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $query->orderByRaw("{$effective} asc"),
            'price_desc' => $query->orderByRaw("{$effective} desc"),
            'name' => $query->orderBy('name'),
            default => $query->latest('id'),
        };

        return view('products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::query()->active()->orderBy('sort_order')->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $related = Product::query()->active()
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->latest()->take(4)->get();

        return view('products.show', ['product' => $product->load(['category', 'variants']), 'related' => $related->load('variants')]);
    }

    /** رقم آمن للتضمين في SQL (PDO يربط الأعداد العشرية كنص فيفسد مقارنة SQLite). */
    private function number(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
