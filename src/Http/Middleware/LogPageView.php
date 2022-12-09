<?php

namespace Uneca\Chimera\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogPageView
{
    public function handle(Request $request, Closure $next)
    {
        $lastEntry = $request->user()->UsageStats()->latest()->first();
        $currentEvent = 'Visited ' . $request->path();
        if ($request->has('page')) {
            $currentEvent .= ', page ' . $request->get('page');
        }
        if (($lastEntry->event ?? null) !== $currentEvent) {
            $request->user()->UsageStats()->create([
                'event' => $currentEvent
            ]);
        }
        return $next($request);
    }
}
