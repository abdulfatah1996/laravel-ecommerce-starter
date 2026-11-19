<?php

namespace App\Http\Middleware;

use App\Models\StoreSetting;
use Closure;
use DateTimeZone;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** يطبّق المنطقة الزمنية المحفوظة في إعدادات المتجر على الطلب الحالي. */
class ApplyStoreSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = StoreSetting::current();

        if ($settings->exists && in_array($settings->timezone, DateTimeZone::listIdentifiers(), true)) {
            config(['app.timezone' => $settings->timezone]);
            date_default_timezone_set($settings->timezone);
        }

        return $next($request);
    }
}
