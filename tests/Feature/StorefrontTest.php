<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->installStore(['name' => 'متجر الاختبار']);
    }

    public function test_home_page_renders_rtl_with_store_name_and_colors(): void
    {
        $this->installStoreColors();

        $this->get('/')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('متجر الاختبار')
            ->assertSee('--store-primary: #123456', false);
    }

    private function installStoreColors(): void
    {
        \App\Models\StoreSetting::query()->update(['primary_color' => '#123456']);
        \Illuminate\Support\Facades\Cache::forget(\App\Models\StoreSetting::CACHE_KEY);
    }

    public function test_product_listing_filters_by_search_category_price_and_stock(): void
    {
        $cat = Category::factory()->create(['slug' => 'cat-a']);
        Product::factory()->create(['name' => 'قميص أزرق', 'price' => 10, 'category_id' => $cat->id]);
        Product::factory()->create(['name' => 'حذاء رياضي', 'price' => 100]);
        Product::factory()->outOfStock()->create(['name' => 'قميص نافد', 'price' => 12]);

        $this->get('/products?q=قميص')->assertSee('قميص أزرق')->assertDontSee('حذاء رياضي');
        $this->get('/products?category=cat-a')->assertSee('قميص أزرق')->assertDontSee('حذاء رياضي');
        $this->get('/products?min_price=50')->assertSee('حذاء رياضي')->assertDontSee('قميص أزرق');
        $this->get('/products?availability=in_stock&q=قميص')->assertSee('قميص أزرق')->assertDontSee('قميص نافد');
    }

    public function test_inactive_products_are_hidden(): void
    {
        $product = Product::factory()->create(['is_active' => false, 'name' => 'مخفي']);

        $this->get('/products')->assertDontSee('مخفي');
        $this->get(route('products.show', $product->slug))->assertNotFound();
    }

    public function test_product_page_shows_price_and_availability(): void
    {
        $product = Product::factory()->create(['name' => 'منتج للعرض', 'price' => 19.5, 'stock' => 3]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('منتج للعرض')
            ->assertSee('19.50')
            ->assertSee('متوفر');
    }

    public function test_static_pages_render_markdown_and_strip_raw_html(): void
    {
        Page::create(['slug' => 'about', 'title' => 'من نحن', 'body' => "## عنوان\n\n<script>alert(1)</script>نص", 'is_published' => true]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('عنوان')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_unpublished_page_is_not_found(): void
    {
        Page::create(['slug' => 'privacy', 'title' => 'x', 'body' => 'y', 'is_published' => false]);

        $this->get('/privacy')->assertNotFound();
    }
}
