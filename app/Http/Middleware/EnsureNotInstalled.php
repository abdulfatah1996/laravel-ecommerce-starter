<?php

namespace App\Http\Middleware;

use App\Support\InstallState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** بعد اكتمال التثبيت تصبح صفحة /install غير موجودة (404). */
class EnsureNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(InstallState::isInstalled(), 404);

        return $next($request);
    }
}
