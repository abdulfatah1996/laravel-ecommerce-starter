<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// التثبيت الأول: يعمل مرة واحدة ثم يصبح 404 (middleware not.installed).
Route::middleware('not.installed')->group(function () {
    Route::get('/install', [InstallController::class, 'show'])->name('install.show');
    Route::post('/install', [InstallController::class, 'store'])
        ->middleware('throttle:install')
        ->name('install.store');
});

// واجهة المتجر: قبل التثبيت تُحوَّل إلى /install.
Route::middleware('installed')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product:id}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{product:id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product:id}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:checkout')
        ->name('checkout.store');
    Route::get('/order/success', [CheckoutController::class, 'success'])->name('checkout.success');

    // من نحن / اتصل بنا / الخصوصية / الاستبدال والاسترجاع (محتواها قابل للتعديل من اللوحة)
    Route::get('/{slug}', [PageController::class, 'show'])
        ->whereIn('slug', ['about', 'contact', 'privacy', 'returns'])
        ->name('pages.show');
});
