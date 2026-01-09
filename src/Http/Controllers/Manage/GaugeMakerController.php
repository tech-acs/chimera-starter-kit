<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateGaugeAction;
use App\Http\Controllers\Controller;
use Uneca\Chimera\DTOs\GaugeAttributes;
use Uneca\Chimera\Http\Requests\GaugeMakerRequest;
use Uneca\Chimera\Models\DataSource;

class GaugeMakerController extends Controller
{
    public function create()
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            return redirect()->route('gauge.index')
                ->withMessage('You have not yet added data sources to your dashboard. Please do so first.');
        }
        return view('chimera::gauge.create', [
            'dataSources' => $dataSources->pluck('title', 'name')->toArray(),
        ]);
    }

    public function store(GaugeMakerRequest $request, CreateGaugeAction $createGaugeAction)
    {
        $gaugeAttributes = new GaugeAttributes(
            name: $request->gauge_name,
            title: $request->title,
            subtitle: $request->subtitle,
            dataSource: $request->data_source,
            stub: resource_path("stubs/gauges/default.stub")
        );
        try {
            $createGaugeAction->execute($gaugeAttributes);
            return redirect()->route('gauge.index')->withMessage('Gauge created');

        } catch (\Exception) {
            return redirect()->route('gauge.index')->withErrors('There was a problem creating the gauge.');
        }
    }
}
