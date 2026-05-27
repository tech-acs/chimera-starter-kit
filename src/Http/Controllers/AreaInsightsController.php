<?php

namespace Uneca\Chimera\Http\Controllers;

use Illuminate\Routing\Controller;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Services\APCA;
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
                    'description' => $dataSource->start_date->toFormattedDateString().' - '.$dataSource->end_date->toFormattedDateString(),
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
        // Filtering by permission is being done in the DataSource model (the attributes: gauges, indicators, area_insights_scorecards...)
        return view('chimera::area-insights.show', compact('dataSource'));
    }
}
