<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
                ->withErrors('This account has been suspended. Please contact the administrator.');
        }
        return $next($request);
    }
}
