<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TranslationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $appLocale = session('app_locale', config('app.locale'));
        if ($request->user()) {
            $appLocale = $request->user()->pref_lang;
        }

        app()->setLocale($appLocale);

        return $next($request);
    }
}
