<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $n = fake()->unique()->numberBetween(1, 1000000);

        return [
            'category_id' => Category::factory(),
            'name' => 'منتج '.$n,
            'slug' => 'product-'.$n,
            'sku' => 'SKU-'.$n,
            'description' => 'وصف المنتج.',
            'price' => fake()->randomFloat(2, 5, 500),
            'compare_at_price' => null,
            'stock' => 10,
            'images' => [],
            'is_active' => true,
            'is_featured' => false,
            'is_demo' => false,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
