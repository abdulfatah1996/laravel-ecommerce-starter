<?php

use App\Http\Middleware\ApplyStoreSettings;
use App\Http\Middleware\EnsureInstalled;
use App\Http\Middleware\EnsureNotInstalled;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // خلف وكيل الاستضافة (Hostinger/Cloudflare) لمعرفة أن الاتصال HTTPS.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            SecurityHeaders::class,
            ApplyStoreSettings::class,
        ]);

        $middleware->alias([
            'installed' => EnsureInstalled::class,
            'not.installed' => EnsureNotInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // لا نعيد عرض رمز التثبيت في النماذج بعد الأخطاء.
        $exceptions->dontFlash(['install_token']);
    })->create();
