<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Enums\ScorecardScope;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Models\Indicator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\AreaTree;

class AreaInsightsController extends Controller
{
    public function __invoke()
    {
        /*try {
            Gate::authorize($indicator->permission_name, Auth::user());
        } catch (AuthorizationException $authorizationException) {
            abort(404);
        }
        if (request()->has('linked_from_scorecard')) {
            session()->forget('area-filter');
        }*/

        $dataSources = DataSource::active()->showOnHomePage()->orderBy('rank')->get();
        //$current = empty($record->path) ? 'National' : ucfirst($hierarchies[AreaTree::levelFromPath($record->path)] ?? $record->path);

        $indicators = Indicator::published()
            //->scope(ScorecardScope::AreaInsights)
            ->orderBy('featured_at', 'DESC')
            ->take(config('chimera.featured_indicators_per_data_source'))
            ->get();

        $scorecards = Scorecard::published()
            ->scope(ScorecardScope::AreaInsights)
            ->orderBy('rank')
            ->get()
            ->filter(function ($scorecard) {
                return Gate::allows($scorecard->permission_name);
            });
        $gauges = Gauge::published()
            ->orderBy('rank')
            ->get()
            ->filter(function ($gauge) {
                return Gate::allows($gauge->permission_name);
            });
        return view('chimera::area-insights.index', compact('dataSources', 'scorecards', 'gauges', 'indicators'));
    }
}
