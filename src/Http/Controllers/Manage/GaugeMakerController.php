<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Routing\Controller;
use Uneca\Chimera\DTOs\GaugeAttributes;
use Uneca\Chimera\Http\Requests\GaugeMakerRequest;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Gauge;

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

    public function store(GaugeMakerRequest $request, CreateArtefactAction $createArtefactAction)
    {
        $validated = $request->validated();
        $gaugeAttributes = new GaugeAttributes(
            name: $validated['name'],
            title: $validated['title'],
            subtitle: $validated['subtitle'],
            dataSource: $validated['data_source'],
            stub: resource_path('stubs/gauges/default.stub')
        );
        $result = $createArtefactAction->execute(modelClass: Gauge::class, baseNamespace: 'Livewire\Gauge', attributes: $gaugeAttributes);
        if ($result->success) {
            return redirect()->route('gauge.index')->withMessage('Gauge created');
        }
        return redirect()->route('gauge.index')->withErrors('There was a problem creating the gauge.');
    }
}
