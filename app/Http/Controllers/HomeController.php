<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $settings = StoreSetting::current();
        $hero = ($settings->hero_product_id ? Product::query()->active()->find($settings->hero_product_id) : null)
            ?? Product::query()->active()->where('is_featured', true)->whereNotNull('images')->latest()->get()->first(fn (Product $p) => $p->primary_image_url)
            ?? Product::query()->active()->latest()->get()->first(fn (Product $p) => $p->primary_image_url);

        return view('home', [
            'hero' => $hero,
            'categories' => Category::query()->active()->orderBy('sort_order')->orderBy('name')->take(8)->get(),
            'featured' => Product::query()->active()->where('is_featured', true)->latest()->take(8)->with('variants')->get(),
            'latest' => Product::query()->active()->with('variants')->latest()->take(8)->get(),
        ]);
    }
}
