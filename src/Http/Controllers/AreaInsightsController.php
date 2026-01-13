<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Enums\PageableTypes;
use Uneca\Chimera\Enums\ScorecardScope;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Models\Indicator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\APCA;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\ColorPalette;

class AreaInsightsController extends Controller
{
    public function index()
    {
        $currentPalette = ColorPalette::current()->colors;
        $totalColors = count($currentPalette);
        $dataSources = DataSource::active()
            ->get()
            ->map(function ($dataSource, $index) use ($totalColors, $currentPalette) {
                return [
                    'title' => $dataSource->title,
                    'description' => $dataSource->start_date->toFormattedDateString() . ' - ' . $dataSource->end_date->toFormattedDateString(),
                    'link' => route('area-insights.show', $dataSource),
                    'slug' => $dataSource->name,
                    'bg-color' => $currentPalette[$index % $totalColors],
                    'fg-color' => APCA::decideBlackOrWhiteTextColor($currentPalette[$index]),
                ];
            })
            ->all();

        return view('chimera::area-insights.index', compact('dataSources'));
    }

    public function show(DataSource $dataSource)
    {
        return view('chimera::area-insights.show', compact('dataSource'));
    }
}
