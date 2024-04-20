<?php

namespace Uneca\Chimera\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIf2FAEnforced
{
    public function handle(Request $request, Closure $next)
    {
        if (config('chimera.enforce_2fa') && is_null(Auth::user()->two_factor_secret)) {
            return to_route('profile.show')
                ->dangerBanner("Before proceeding any further you are required to enable two factor authentication. Please do so below!");
        }

        return $next($request);
    }
}
