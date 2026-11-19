<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'categories' => Category::query()->active()->orderBy('sort_order')->orderBy('name')->take(8)->get(),
            'featured' => Product::query()->active()->where('is_featured', true)->latest()->take(8)->get(),
            'latest' => Product::query()->active()->latest()->take(8)->get(),
        ]);
    }
}
