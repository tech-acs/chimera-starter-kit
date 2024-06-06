<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Analytics;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\AreaTree;

class AnalyticsController extends Controller
{
    public function index()
    {
        $hierarchies = (new AreaTree())->hierarchies;
        $records = Analytics::query()
            ->with('analyzable', 'user')
            //->selectRaw('user_id, analyzable_type, analyzable_id, started_at, elapsed_seconds')
            ->orderBy('started_at', 'DESC')
            ->paginate(config('chimera.records_per_page'));
        $records->setCollection(
            $records->getCollection()->map(function ($record) use ($hierarchies) {
                $class = class_basename($record->analyzable_type);
                $record->path = empty($record->path) ? 'National' : ucfirst($hierarchies[AreaTree::levelFromPath($record->path)] ?? $record->path);
                $record->type = $class == 'DataSource' ? 'CaseStats' : $class;
                $record->icon_component = match($class) {
                    'Indicator' => 'chimera::icon.indicator',
                    'Scorecard' => 'chimera::icon.scorecard',
                    'MapIndicator' => 'chimera::icon.map-indicator',
                    'DataSource' => 'chimera::icon.case-stats',
                    default => 'chimera::icon.indicator'
                };
                return $record;
            })
        );
        return view('chimera::analytics.index', compact('records'));
    }

    public function show(Indicator $indicator)
    {
        $hierarchies = (new AreaTree())->hierarchies;
        $longestRunningQueries = $indicator->analytics()
            //->selectRaw('user_id, started_at, level, source, completed_at - started_at AS query_time')
            ->orderBy('elapsed_seconds', 'DESC')
            ->take(5)
            ->get()
            ->map(function ($record) use ($hierarchies) {
                $record->level = is_null($record->level) ? 'National' : ucfirst($hierarchies[AreaTree::levelFromPath($record->path)] ?? $record->path);
                return $record;
            });
        $queryTimes = $indicator->analytics()
            //->selectRaw('completed_at - started_at AS query_time')
            ->orderBy('started_at')
            ->get()
            ->pluck('elapsed_seconds');
        return view('chimera::indicator.analytics', compact('indicator', 'longestRunningQueries', 'queryTimes'));
    }
}
