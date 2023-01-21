<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\AreaTree;

class IndicatorAnalyticsController extends Controller
{
    public function __invoke(Indicator $indicator)
    {
        $hierarchies = (new AreaTree())->hierarchies;
        $longestRunningQueries = $indicator->analytics()
            ->selectRaw('user_id, started_at, level, source, completed_at - started_at AS query_time')
            ->orderBy('query_time', 'DESC')
            ->take(5)
            ->get()
            ->map(function ($record) use ($hierarchies) {
                $record->level = is_null($record->level) ? 'National' : ucfirst($hierarchies[$record->level - 1]);
                return $record;
            });
        $queryTimes = $indicator->analytics()
            ->selectRaw('completed_at - started_at AS query_time')
            ->orderBy('started_at')
            ->get()
            ->pluck('query_time');
        return view('chimera::indicator.analytics', compact('indicator', 'longestRunningQueries', 'queryTimes'));
    }
}
