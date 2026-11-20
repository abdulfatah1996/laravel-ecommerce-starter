<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->installStore(['shipping_fee' => 5, 'whatsapp_number' => '970599123456', 'tagline' => 'إضاءة دافئة']);
    }

    private function data(string $token, array $o = []): array
    {
        return array_merge([
            'name' => 'زبون', 'phone' => '0590000000', 'city' => 'رام الله', 'address' => 'شارع 1',
            'payment_method' => 'cod', 'checkout_token' => $token,
        ], $o);
    }

    private function token(): string
    {
        $this->get('/checkout')->assertOk();

        return (string) session('checkout.token');
    }

    private function variantProduct(): Product
    {
        $p = Product::factory()->create(['price' => 129, 'stock' => 0, 'option_name' => 'المقاس']);
        $p->variants()->create(['label' => 'صغير', 'price' => 89, 'stock' => 6]);
        $p->variants()->create(['label' => 'كبير', 'price' => null, 'stock' => 2]);

        return $p->load('variants');
    }

    public function test_home_has_hero_headline_and_collection_button(): void
    {
        $this->get('/')->assertOk()->assertSee('إضاءة دافئة')->assertSee('تسوّق المجموعة')
            ->assertSee(route('products.index', ['collection' => 'featured']), false);
    }

    public function test_variant_selection_uses_variant_price_and_decrements_only_that_variant(): void
    {
        $p = $this->variantProduct();
        [$small, $large] = [$p->variants[0], $p->variants[1]];

        // بدون اختيار خيار: ترفض الإضافة
        $this->post(route('cart.add', $p->id), ['quantity' => 1])->assertSessionHas('error');
        $this->assertTrue(app(\App\Services\Cart::class)->isEmpty());

        $this->post(route('cart.add', $p->id), ['quantity' => 2, 'variant_id' => $small->id]);
        $token = $this->token();
        $this->post('/checkout', $this->data($token, ['price' => 1]))->assertRedirect(route('checkout.success'));

        $order = Order::with('items')->firstOrFail();
        $this->assertEquals(178.00, (float) $order->subtotal);       // 2 × 89
        $this->assertEquals(183.00, (float) $order->total);          // + شحن 5
        $this->assertSame('صغير', $order->items->first()->variant_label);
        $this->assertSame(4, $small->fresh()->stock);
        $this->assertSame(2, $large->fresh()->stock);
    }

    public function test_out_of_stock_variant_cannot_be_added_or_ordered(): void
    {
        $p = $this->variantProduct();
        $large = $p->variants[1];
        $large->update(['stock' => 0]);

        $this->post(route('cart.add', $p->id), ['variant_id' => $large->id])->assertSessionHas('error');

        // نضعه في السلة ثم ينفد المخزون قبل التأكيد: لا طلب ولا خصم
        $this->post(route('cart.add', $p->id), ['variant_id' => $p->variants[0]->id, 'quantity' => 3]);
        $token = $this->token();
        $p->variants[0]->update(['stock' => 1]);
        $this->post('/checkout', $this->data($token))->assertRedirect(route('cart.index'))->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(1, $p->variants[0]->fresh()->stock);
    }

    public function test_double_submit_creates_one_order_and_deducts_once(): void
    {
        $p = Product::factory()->create(['price' => 10, 'stock' => 5]);
        $this->post(route('cart.add', $p->id), ['quantity' => 2]);
        $token = $this->token();

        $this->post('/checkout', $this->data($token));
        $this->post('/checkout', $this->data($token));

        $this->assertSame(1, Order::count());
        $this->assertSame(3, $p->fresh()->stock);
    }

    public function test_shipping_zone_fee_is_applied_server_side(): void
    {
        $zone = ShippingZone::create(['name' => 'غزة', 'fee' => 35, 'is_active' => true]);
        ShippingZone::create(['name' => 'الضفة', 'fee' => 20, 'is_active' => true]);
        $p = Product::factory()->create(['price' => 100, 'stock' => 5]);
        $this->post(route('cart.add', $p->id));
        $token = $this->token();

        // بدون منطقة: مرفوض
        $this->post('/checkout', $this->data($token))->assertRedirect(route('cart.index'));
        $this->assertSame(0, Order::count());

        $token = $this->token();
        $this->post('/checkout', $this->data($token, ['shipping_zone_id' => $zone->id]))->assertRedirect(route('checkout.success'));
        $order = Order::firstOrFail();
        $this->assertEquals(35.00, (float) $order->shipping_fee);
        $this->assertEquals(135.00, (float) $order->total);
        $this->assertSame('غزة', $order->shipping_zone);
    }

    public function test_cart_line_update_and_remove_by_key(): void
    {
        $p = $this->variantProduct();
        $v = $p->variants[0];
        $this->post(route('cart.add', $p->id), ['variant_id' => $v->id]);
        $key = $p->id.':'.$v->id;

        $this->patch(route('cart.update', $key), ['quantity' => 3])->assertRedirect(route('cart.index'));
        $this->get(route('cart.index'))->assertOk()->assertSee('صغير');
        $this->delete(route('cart.remove', $key));
        $this->get(route('cart.index'))->assertSee('سلتك فارغة');
    }

    public function test_price_filter_and_search_use_variant_prices(): void
    {
        $p = $this->variantProduct(); // يبدأ من 89
        $this->get('/products?max_price=90')->assertSee($p->name);
        $this->get('/products?min_price=100')->assertDontSee($p->name);
    }

    public function test_empty_results_offer_clear_filters(): void
    {
        $this->get('/products?q=لا-شيء-يطابق')->assertOk()->assertSee('لا توجد منتجات مطابقة')->assertSee('مسح الفلاتر');
    }

    public function test_registered_customer_sees_only_own_orders_and_cannot_enter_admin(): void
    {
        $this->post(route('account.register.store'), ['name' => 'سارة', 'email' => 's@example.com', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1'])
            ->assertRedirect(route('account.orders'));
        $user = User::where('email', 's@example.com')->firstOrFail();
        $this->assertNull($user->role);
        $this->assertFalse($user->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')));

        $p = Product::factory()->create(['price' => 10, 'stock' => 5]);
        $this->post(route('cart.add', $p->id));
        $this->post('/checkout', $this->data($this->token()))->assertRedirect(route('checkout.success'));
        $mine = Order::firstOrFail();
        $this->assertSame($user->id, $mine->user_id);

        $other = Order::create(['number' => 'ORD-OTHER', 'status' => 'pending', 'payment_method' => 'cod', 'payment_status' => 'unpaid', 'subtotal' => 1, 'total' => 1, 'currency_code' => 'USD', 'customer_name' => 'x', 'customer_phone' => '1', 'city' => 'c', 'address' => 'a']);

        $this->get(route('account.orders'))->assertOk()->assertSee($mine->number)->assertDontSee('ORD-OTHER');
        $this->get(route('account.order', $mine->number))->assertOk();
        $this->get(route('account.order', $other->number))->assertNotFound();
    }

    public function test_guest_cannot_open_account_orders(): void
    {
        $this->get(route('account.orders'))->assertRedirect(route('login'));
    }

    public function test_primary_color_and_logo_changes_show_immediately(): void
    {
        $this->get('/')->assertSee('--store-primary: #0f766e', false);
        \App\Models\StoreSetting::query()->first()->update(['primary_color' => '#112233']);
        $this->get('/')->assertSee('--store-primary: #112233', false);
    }

    public function test_whatsapp_link_is_digits_only(): void
    {
        $this->assertSame('https://wa.me/970599123456', \App\Models\StoreSetting::current()->whatsappUrl());
        $this->get('/')->assertSee('https://wa.me/970599123456', false);
    }

    public function test_cancelling_order_restores_variant_stock(): void
    {
        $p = $this->variantProduct();
        $v = $p->variants[0];
        $this->post(route('cart.add', $p->id), ['variant_id' => $v->id, 'quantity' => 2]);
        $this->post('/checkout', $this->data($this->token()));
        $this->assertSame(4, $v->fresh()->stock);

        Order::firstOrFail()->update(['status' => 'cancelled']);
        $this->assertSame(6, $v->fresh()->stock);
    }
}
