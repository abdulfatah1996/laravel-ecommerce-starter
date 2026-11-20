<?php

use App\Http\Controllers\AccountController;
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
    Route::patch('/cart/{key}', [CartController::class, 'update'])->where('key', '[0-9:]+')->name('cart.update');
    Route::delete('/cart/{key}', [CartController::class, 'remove'])->where('key', '[0-9:]+')->name('cart.remove');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:checkout')
        ->name('checkout.store');
    Route::get('/order/success', [CheckoutController::class, 'success'])->name('checkout.success');

    // حساب الزبون
    Route::middleware('guest')->group(function () {
        Route::get('/account/login', [AccountController::class, 'loginForm'])->name('login');
        Route::post('/account/login', [AccountController::class, 'login'])->middleware('throttle:10,1')->name('account.login');
        Route::get('/account/register', [AccountController::class, 'registerForm'])->name('account.register');
        Route::post('/account/register', [AccountController::class, 'register'])->middleware('throttle:10,1')->name('account.register.store');
    });
    Route::middleware('auth')->group(function () {
        Route::post('/account/logout', [AccountController::class, 'logout'])->name('account.logout');
        Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
        Route::get('/account/orders/{order:number}', [AccountController::class, 'show'])->name('account.order');
    });

    // من نحن / اتصل بنا / الخصوصية / الاستبدال والاسترجاع (محتواها قابل للتعديل من اللوحة)
    Route::get('/{slug}', [PageController::class, 'show'])
        ->whereIn('slug', ['about', 'contact', 'privacy', 'returns', 'shipping'])
        ->name('pages.show');
});
