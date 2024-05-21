<?php

namespace Uneca\Chimera\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class Language
{
    public function handle($request, Closure $next)
    {
        $header = $request->server->get('HTTP_ACCEPT_LANGUAGE');
        $userPreferredLocale = collect(explode(',', $header))
            ->map(fn ($locale) => str($locale)->replace('-', '_'))
            ->first();
        $lang = Cookie::get('locale', (string)$userPreferredLocale);
        if (array_key_exists($lang, config('languages'))) {
            App::setLocale($lang);
            Cookie::queue('locale', $lang);
        } else {
            App::setLocale('en');
        }
        return $next($request);
    }
}
