<?php

namespace Uneca\Chimera\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAccountSuspension
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && (auth()->user()->is_suspended)){
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()
                ->route('login')
                ->withInput(['email' => auth()->user()->email])
                ->withErrors('This account is not ready for use. Please contact the administrator.');
        }
        return $next($request);
    }
}
