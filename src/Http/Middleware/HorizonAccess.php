<?php

namespace Uneca\Chimera\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HorizonAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (! session('developer_mode_enabled', false)) {
            abort(403);
        }

        return $next($request);
    }
}
