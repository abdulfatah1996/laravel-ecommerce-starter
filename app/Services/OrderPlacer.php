<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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
     * @param  array{name:string,phone:string,email:?string,city:string,address:string,notes:?string,payment_method:string}  $data
     * @param  array<int,int>  $cartItems  معرّف المنتج => الكمية
     *
     * @throws CheckoutException
     */
    public function place(array $data, array $cartItems, string $token, StoreSetting $settings): Order
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

        try {
            return DB::transaction(function () use ($data, $cartItems, $token, $settings, $method) {
                $products = Product::query()
                    ->active()
                    ->whereIn('id', array_keys($cartItems))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $lines = [];
                $subtotal = 0.0;

                foreach ($cartItems as $productId => $quantity) {
                    $product = $products[$productId] ?? null;

                    if ($product === null) {
                        throw new CheckoutException('أحد المنتجات لم يعد متاحًا. راجع سلتك.');
                    }
                    if ($product->stock < $quantity) {
                        throw new CheckoutException("الكمية المطلوبة من «{$product->name}» غير متوفرة (المتاح: {$product->stock}).");
                    }

                    $lineTotal = round((float) $product->price * $quantity, 2);
                    $subtotal += $lineTotal;
                    $lines[] = [$product, $quantity, $lineTotal];
                }

                $subtotal = round($subtotal, 2);
                $shipping = round((float) $settings->shipping_fee, 2);

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
                    'total' => round($subtotal + $shipping, 2),
                    'currency_code' => $settings->currency_code,
                    'customer_name' => $data['name'],
                    'customer_phone' => $data['phone'],
                    'customer_email' => $data['email'] ?? null,
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($lines as [$product, $quantity, $lineTotal]) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'unit_price' => $product->price,
                        'quantity' => $quantity,
                        'line_total' => $lineTotal,
                    ]);

                    $product->decrement('stock', $quantity);
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
