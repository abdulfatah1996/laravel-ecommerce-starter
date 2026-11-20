<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * سلة المشتريات في الجلسة. تخزّن فقط (مفتاح السطر => الكمية)، ومفتاح السطر
 * "معرّف المنتج" أو "معرّف المنتج:معرّف الخيار". الأسعار والمخزون تُقرأ دائمًا
 * من قاعدة البيانات ولا يُوثق بأي قيمة من المتصفح.
 */
class Cart
{
    private const KEY = 'cart.items';

    public function __construct(private readonly Session $session) {}

    public static function key(int $productId, ?int $variantId = null): string
    {
        return $variantId ? "{$productId}:{$variantId}" : (string) $productId;
    }

    /** @return array{0:int,1:int|null} */
    public static function parse(string|int $key): array
    {
        $parts = explode(':', (string) $key);

        return [(int) $parts[0], isset($parts[1]) ? (int) $parts[1] : null];
    }

    /** @return array<string|int,int> */
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

    public function add(int $productId, int $quantity, ?int $variantId = null): void
    {
        $items = $this->items();
        $key = self::key($productId, $variantId);
        $items[$key] = $this->clamp(($items[$key] ?? 0) + $quantity);
        $this->session->put(self::KEY, $items);
    }

    public function set(string $key, int $quantity): void
    {
        $items = $this->items();
        if (! array_key_exists($key, $items)) {
            return;
        }
        $items[$key] = $this->clamp($quantity);
        $this->session->put(self::KEY, $items);
    }

    public function remove(string $key): void
    {
        $items = $this->items();
        unset($items[$key]);
        $this->session->put(self::KEY, $items);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    /**
     * أسطر السلة محسوبة من قاعدة البيانات. المنتجات/الخيارات المحذوفة أو غير المفعّلة تُزال.
     *
     * @return Collection<int,array{key:string,product:Product,variant:?ProductVariant,quantity:int,unit_price:float,line_total:float,stock:int,exceeds_stock:bool}>
     */
    public function lines(): Collection
    {
        $items = $this->items();
        if ($items === []) {
            return collect();
        }

        $parsed = [];
        foreach ($items as $key => $quantity) {
            [$pid, $vid] = self::parse($key);
            $parsed[(string) $key] = [$pid, $vid, (int) $quantity];
        }

        $products = Product::query()->active()->with('variants')
            ->whereIn('id', array_unique(array_column($parsed, 0)))->get()->keyBy('id');

        $valid = [];
        $lines = collect();

        foreach ($parsed as $key => [$pid, $vid, $quantity]) {
            $product = $products[$pid] ?? null;
            if ($product === null) {
                continue;
            }

            $variant = null;
            if ($product->hasVariants()) {
                // منتج له خيارات: لا بد من خيار فعّال صالح.
                $variant = $vid ? $product->variants->where('is_active', true)->firstWhere('id', $vid) : null;
                if ($variant === null) {
                    continue;
                }
            } elseif ($vid !== null) {
                continue;
            }

            $valid[$key] = $quantity;
            $unit = $variant ? $variant->effectivePrice() : (float) $product->price;
            $stock = $variant ? $variant->stock : $product->stock;

            $lines->push([
                'key' => (string) $key,
                'product' => $product,
                'variant' => $variant,
                'quantity' => $quantity,
                'unit_price' => $unit,
                'line_total' => round($unit * $quantity, 2),
                'stock' => $stock,
                'exceeds_stock' => $quantity > $stock,
            ]);
        }

        if (count($valid) !== count($items)) {
            $this->session->put(self::KEY, $valid);
        }

        return $lines->values();
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
