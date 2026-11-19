<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * سلة المشتريات في الجلسة. تخزّن فقط (معرّف المنتج => الكمية).
 * الأسعار والمخزون تُقرأ دائمًا من قاعدة البيانات ولا يُوثق بأي قيمة من المتصفح.
 */
class Cart
{
    private const KEY = 'cart.items';

    public function __construct(private readonly Session $session) {}

    /** @return array<int,int> */
    public function items(): array
    {
        return $this->session->get(self::KEY, []);
    }

    public function count(): int
    {
        return (int) array_sum($this->items());
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    public function add(int $productId, int $quantity): void
    {
        $items = $this->items();
        $items[$productId] = $this->clamp(($items[$productId] ?? 0) + $quantity);
        $this->session->put(self::KEY, $items);
    }

    public function set(int $productId, int $quantity): void
    {
        $items = $this->items();
        if (! isset($items[$productId])) {
            return;
        }
        $items[$productId] = $this->clamp($quantity);
        $this->session->put(self::KEY, $items);
    }

    public function remove(int $productId): void
    {
        $items = $this->items();
        unset($items[$productId]);
        $this->session->put(self::KEY, $items);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    /**
     * أسطر السلة محسوبة من قاعدة البيانات. المنتجات المحذوفة أو غير المفعّلة تُزال من السلة.
     *
     * @return Collection<int,array{product:Product,quantity:int,unit_price:float,line_total:float,exceeds_stock:bool}>
     */
    public function lines(): Collection
    {
        $items = $this->items();
        if ($items === []) {
            return collect();
        }

        $products = Product::query()->active()->whereIn('id', array_keys($items))->get()->keyBy('id');

        $valid = array_intersect_key($items, $products->all());
        if (count($valid) !== count($items)) {
            $this->session->put(self::KEY, $valid);
        }

        return collect($valid)->map(function (int $quantity, int $id) use ($products) {
            $product = $products[$id];
            $unit = (float) $product->price;

            return [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unit,
                'line_total' => round($unit * $quantity, 2),
                'exceeds_stock' => $quantity > $product->stock,
            ];
        })->values();
    }

    public function subtotal(): float
    {
        return round((float) $this->lines()->sum('line_total'), 2);
    }

    private function clamp(int $quantity): int
    {
        return max(1, min($quantity, (int) config('store.max_cart_quantity', 20)));
    }
}
