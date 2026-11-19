<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Support\Slug;
use Illuminate\Database\Seeder;

/**
 * بيانات تجريبية واضحة (موسومة is_demo) لتجربة القالب فقط.
 * احذفها قبل التسليم: php artisan store:demo-clean
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'منتجات تجريبية — الفئة أ' => [
                ['منتج تجريبي 1', 25.00, 40.00, 12, true],
                ['منتج تجريبي 2', 60.00, null, 5, true],
                ['منتج تجريبي 3', 15.50, null, 0, false],
            ],
            'منتجات تجريبية — الفئة ب' => [
                ['منتج تجريبي 4', 120.00, 150.00, 8, true],
                ['منتج تجريبي 5', 9.99, null, 30, false],
                ['منتج تجريبي 6', 45.00, null, 3, false],
            ],
            'منتجات تجريبية — الفئة ج' => [
                ['منتج تجريبي 7', 200.00, 250.00, 2, true],
                ['منتج تجريبي 8', 35.00, null, 20, false],
            ],
        ];

        $order = 0;
        foreach ($catalog as $categoryName => $products) {
            $category = Category::updateOrCreate(
                ['slug' => Slug::make($categoryName)],
                ['name' => $categoryName, 'sort_order' => $order++, 'is_active' => true, 'is_demo' => true]
            );

            foreach ($products as $i => [$name, $price, $compare, $stock, $featured]) {
                Product::updateOrCreate(
                    ['slug' => Slug::make($name)],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'sku' => 'DEMO-'.$category->id.'-'.($i + 1),
                        'description' => 'هذا منتج تجريبي لعرض شكل المتجر. احذفه قبل التسليم.',
                        'price' => $price,
                        'compare_at_price' => $compare,
                        'stock' => $stock,
                        'is_active' => true,
                        'is_featured' => $featured,
                        'is_demo' => true,
                    ]
                );
            }
        }
    }
}
