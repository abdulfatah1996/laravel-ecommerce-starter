<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\StoreSetting;
use App\Payments\PaymentRegistry;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class OrderPlacer
{
    public function __construct(private readonly PaymentRegistry $payments) {}

    /**
     * ينشئ الطلب بالكامل داخل معاملة واحدة. كل الأسعار والمخزون تُقرأ من قاعدة البيانات
     * (مع قفل الصفوف) ولا يُستخدم أي سعر قادم من المتصفح.
     *
     * @param  array{shipping_zone_id?:?int,name:string,phone:string,email:?string,city:string,address:string,notes:?string,payment_method:string}  $data
     * @param  array<string|int,int>  $cartItems  مفتاح السطر (منتج أو منتج:خيار) => الكمية
     *
     * @throws CheckoutException
     */
    public function place(array $data, array $cartItems, string $token, StoreSetting $settings, ?int $userId = null): Order
    {
        if ($cartItems === []) {
            throw new CheckoutException('السلة فارغة.');
        }

        // طلب مكرر (ضغط مزدوج): نرجع الطلب الموجود بدل إنشاء آخر.
        if ($existing = Order::query()->where('checkout_token', $token)->first()) {
            return $existing;
        }

        $method = $this->payments->enabled($settings)[$data['payment_method']] ?? null;
        if ($method === null) {
            throw new CheckoutException('طريقة الدفع المختارة غير متاحة.');
        }

        $zone = null;
        $zones = ShippingZone::query()->where('is_active', true)->get();
        if ($zones->isNotEmpty()) {
            $zone = $zones->firstWhere('id', (int) ($data['shipping_zone_id'] ?? 0));
            if ($zone === null) {
                throw new CheckoutException('اختر منطقة التوصيل.');
            }
        }

        try {
            return DB::transaction(function () use ($data, $cartItems, $token, $settings, $method, $zone, $userId) {
                $productIds = collect(array_keys($cartItems))->map(fn ($k) => Cart::parse($k)[0])->unique()->all();

                $products = Product::query()
                    ->active()
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $variants = ProductVariant::query()
                    ->whereIn('product_id', $productIds)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $lines = [];
                $subtotal = 0.0;
                $needed = []; // لا نخصم مرتين من نفس الصف عند تكرار المنتج/الخيار

                foreach ($cartItems as $key => $quantity) {
                    [$productId, $variantId] = Cart::parse($key);
                    $product = $products[$productId] ?? null;

                    if ($product === null) {
                        throw new CheckoutException('أحد المنتجات لم يعد متاحًا. راجع سلتك.');
                    }

                    $variant = null;
                    $hasVariants = $variants->where('product_id', $productId)->isNotEmpty();
                    if ($hasVariants) {
                        $variant = $variantId ? $variants->get($variantId) : null;
                        if ($variant === null || (int) $variant->product_id !== (int) $product->id) {
                            throw new CheckoutException("اختر خيار «{$product->name}» من جديد، فقد تغيّر المتاح.");
                        }
                    } elseif ($variantId !== null) {
                        throw new CheckoutException('أحد المنتجات لم يعد متاحًا. راجع سلتك.');
                    }

                    $stockKey = $variant ? "v{$variant->id}" : "p{$product->id}";
                    $available = $variant ? $variant->stock : $product->stock;
                    $needed[$stockKey] = ($needed[$stockKey] ?? 0) + $quantity;
                    $label = $variant ? "{$product->name} ({$variant->label})" : $product->name;

                    if ($available < $needed[$stockKey]) {
                        throw new CheckoutException("الكمية المطلوبة من «{$label}» غير متوفرة (المتاح: {$available}).");
                    }

                    $unit = $variant ? $variant->effectivePrice() : (float) $product->price;
                    $lineTotal = round($unit * $quantity, 2);
                    $subtotal += $lineTotal;
                    $lines[] = [$product, $variant, $quantity, $unit, $lineTotal];
                }

                $subtotal = round($subtotal, 2);
                $shipping = round((float) ($zone ? $zone->fee : $settings->shipping_fee), 2);

                $customer = Customer::query()->firstOrNew(['phone' => $data['phone']]);
                $customer->fill([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'city' => $data['city'],
                    'address' => $data['address'],
                ])->save();

                $order = Order::create([
                    'number' => Order::generateNumber(),
                    'checkout_token' => $token,
                    'customer_id' => $customer->id,
                    'payment_method' => $method->key(),
                    'payment_status' => $method->initialPaymentStatus(),
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shipping,
                    'shipping_zone' => $zone?->name,
                    'user_id' => $userId,
                    'total' => round($subtotal + $shipping, 2),
                    'currency_code' => $settings->currency_code,
                    'customer_name' => $data['name'],
                    'customer_phone' => $data['phone'],
                    'customer_email' => $data['email'] ?? null,
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($lines as [$product, $variant, $quantity, $unit, $lineTotal]) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant?->id,
                        'variant_label' => $variant?->label,
                        'name' => $product->name,
                        'sku' => $variant?->sku ?: $product->sku,
                        'unit_price' => $unit,
                        'quantity' => $quantity,
                        'line_total' => $lineTotal,
                    ]);

                    ($variant ?? $product)->decrement('stock', $quantity);
                }

                return $order;
            });
        } catch (UniqueConstraintViolationException) {
            // سباق بين طلبين بنفس الرمز: نرجع الطلب الذي سبق.
            $existing = Order::query()->where('checkout_token', $token)->first();
            if ($existing) {
                return $existing;
            }
            throw new CheckoutException('تعذر إتمام الطلب، حاول مرة أخرى.');
        }
    }
}
