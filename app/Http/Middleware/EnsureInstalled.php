<?php

namespace App\Http\Middleware;

use App\Support\InstallState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** قبل اكتمال التثبيت تُحوَّل واجهة المتجر إلى صفحة /install. */
class EnsureInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! InstallState::isInstalled()) {
            return redirect()->route('install.show');
        }

        return $next($request);
    }
}
