<?php

namespace Uneca\Chimera\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class Language
{
    public function handle($request, Closure $next)
    {
        $lang = Cookie::get('locale', 'en');
        if (array_key_exists($lang, config('languages'))) {
            App::setLocale($lang);
        }
        return $next($request);
    }
}
