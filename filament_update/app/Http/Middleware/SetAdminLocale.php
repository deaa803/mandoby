<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestedLocale = $request->query('lang');

        if (in_array($requestedLocale, ['ar', 'en'], true)) {
            $request->session()->put('admin_locale', $requestedLocale);
        }

        $locale = $request->session()->get('admin_locale', 'ar');

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
