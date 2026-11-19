<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Payments\PaymentRegistry;
use App\Policies\CatalogPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PagePolicy;
use App\Policies\UserPolicy;
use App\Services\Cart;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Cart::class, fn ($app) => new Cart($app['session.store']));

        $this->app->singleton(PaymentRegistry::class, function ($app) {
            return new PaymentRegistry(
                array_map(fn (string $class) => $app->make($class), config('store.payment_methods', []))
            );
        });
    }

    public function boot(): void
    {
        // خلف وكيل HTTPS نُجبر الروابط على https عندما يكون APP_URL آمنًا.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Gate::policy(Product::class, CatalogPolicy::class);
        Gate::policy(Category::class, CatalogPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Order::observe(OrderObserver::class);

        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(6)->by($request->ip()));
        RateLimiter::for('install', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // $store متاح في كل قوالب الواجهة (الأبناء تُعرض قبل القالب الأب، لذا لا يكفي ربطه بالقالب الأب فقط).
        View::composer(
            ['layouts.app', 'partials.*', 'home', 'products.*', 'cart', 'checkout', 'order-success', 'page'],
            fn ($view) => $view->with('store', StoreSetting::current())
        );

        View::composer('layouts.app', fn ($view) => $view->with('cartCount', $this->app->make(Cart::class)->count()));
    }
}
