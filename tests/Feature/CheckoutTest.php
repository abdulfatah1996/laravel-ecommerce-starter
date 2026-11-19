<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->installStore(['shipping_fee' => 5, 'cod_enabled' => true]);
    }

    /** @return array<string,mixed> */
    private function checkoutData(string $token, array $overrides = []): array
    {
        return array_merge([
            'name' => 'زبون',
            'phone' => '0590000000',
            'email' => 'c@example.com',
            'city' => 'غزة',
            'address' => 'شارع 1',
            'notes' => 'ملاحظة',
            'payment_method' => 'cod',
            'checkout_token' => $token,
        ], $overrides);
    }

    private function sessionToken(): string
    {
        $this->get('/checkout')->assertOk();

        return (string) session('checkout.token');
    }

    public function test_order_is_created_with_server_side_prices_and_stock_decrements(): void
    {
        $product = Product::factory()->create(['price' => 20.00, 'stock' => 10]);

        $this->post(route('cart.add', $product->id), ['quantity' => 2])->assertRedirect();
        $token = $this->sessionToken();

        // محاولة تلاعب بالسعر من المتصفح: يجب تجاهلها
        $this->post('/checkout', $this->checkoutData($token, ['price' => 0.01, 'total' => 0.01, 'unit_price' => 0.01]))
            ->assertRedirect(route('checkout.success'));

        $order = Order::query()->with('items')->firstOrFail();
        $this->assertEquals(40.00, (float) $order->subtotal);
        $this->assertEquals(5.00, (float) $order->shipping_fee);
        $this->assertEquals(45.00, (float) $order->total);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(8, $product->fresh()->stock);

        $this->get(route('checkout.success'))->assertOk()->assertSee($order->number);
    }

    public function test_cannot_order_more_than_stock(): void
    {
        $product = Product::factory()->create(['stock' => 1]);

        $this->post(route('cart.add', $product->id), ['quantity' => 3]);
        $token = $this->sessionToken();

        $this->post('/checkout', $this->checkoutData($token))->assertRedirect(route('cart.index'));

        $this->assertSame(0, Order::query()->count());
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_out_of_stock_product_cannot_be_added(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->post(route('cart.add', $product->id), ['quantity' => 1])->assertSessionHas('error');
        $this->get(route('cart.index'))->assertOk()->assertSee('سلتك فارغة');
    }

    public function test_double_submit_does_not_create_two_orders(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->post(route('cart.add', $product->id), ['quantity' => 1]);
        $token = $this->sessionToken();

        $this->post('/checkout', $this->checkoutData($token));
        // الجلسة نظّفت الرمز؛ إعادة إرسال نفس الرمز لا تنشئ طلبًا جديدًا
        $this->post('/checkout', $this->checkoutData($token));

        $this->assertSame(1, Order::query()->count());
    }

    public function test_invalid_checkout_token_creates_no_order(): void
    {
        $product = Product::factory()->create();

        $this->post(route('cart.add', $product->id), ['quantity' => 1]);
        $this->sessionToken();

        $this->post('/checkout', $this->checkoutData('11111111-1111-4111-8111-111111111111'))
            ->assertRedirect(route('checkout.show'));

        $this->assertSame(0, Order::query()->count());
    }

    public function test_honeypot_blocks_bots(): void
    {
        $product = Product::factory()->create();

        $this->post(route('cart.add', $product->id), ['quantity' => 1]);
        $token = $this->sessionToken();

        $this->post('/checkout', $this->checkoutData($token, ['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_disabled_payment_method_is_rejected(): void
    {
        $product = Product::factory()->create();

        $this->post(route('cart.add', $product->id), ['quantity' => 1]);
        $token = $this->sessionToken();

        // الدفع اليدوي غير مفعّل افتراضيًا
        $this->post('/checkout', $this->checkoutData($token, ['payment_method' => 'manual']))
            ->assertRedirect(route('cart.index'));

        $this->assertSame(0, Order::query()->count());
    }

    public function test_success_page_does_not_expose_other_customers_orders(): void
    {
        $other = Order::create([
            'number' => 'ORD-OTHER-1', 'status' => 'pending', 'payment_method' => 'cod', 'payment_status' => 'unpaid',
            'subtotal' => 1, 'shipping_fee' => 0, 'total' => 1, 'currency_code' => 'USD',
            'customer_name' => 'شخص آخر', 'customer_phone' => '111', 'city' => 'x', 'address' => 'y',
        ]);

        // جلسة جديدة بلا طلبات: لا تعرض شيئًا وتعيد للرئيسية
        $this->get(route('checkout.success'))->assertRedirect(route('home'));
        $this->get(route('checkout.success', ['order' => $other->id]))->assertRedirect(route('home'));
    }

    public function test_cancelling_an_order_restores_stock(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.add', $product->id), ['quantity' => 2]);
        $token = $this->sessionToken();
        $this->post('/checkout', $this->checkoutData($token));

        $this->assertSame(3, $product->fresh()->stock);

        Order::query()->firstOrFail()->update(['status' => OrderStatus::Cancelled]);

        $this->assertSame(5, $product->fresh()->stock);
    }
}
