<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Indicator;

class IndicatorAnalyticsController extends Controller
{
    public function __invoke(Indicator $indicator)
    {
        $queryTimes = $indicator->analytics()
            ->selectRaw('completed_at - started_at AS query_time')
            ->orderBy('started_at')
            ->get()
            ->pluck('query_time');
        return view('chimera::indicator.analytics', compact('indicator', 'queryTimes'));
    }
}
