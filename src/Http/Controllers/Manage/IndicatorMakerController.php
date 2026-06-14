<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Routing\Controller;
use Uneca\Chimera\DTOs\IndicatorAttributes;
use Uneca\Chimera\Http\Requests\IndicatorMakerRequest;
use Uneca\Chimera\Models\ChartTemplate;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Traits\PlotlyDefaults;

class IndicatorMakerController extends Controller
{
    use PlotlyDefaults;

    public function create()
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            return redirect()->route('indicator.index')
                ->withMessage('You have not yet added data sources to your dashboard. Please do so first.');
        }

        return view('chimera::indicator.create', [
            'dataSources' => $dataSources->pluck('title', 'name')->toArray(),
            'availableTemplates' => ChartTemplate::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(IndicatorMakerRequest $request, CreateArtefactAction $createArtefactAction)
    {
        $validated = $request->validated();
        $selectedTemplate = ChartTemplate::find($request->selectedTemplateId ?? null);
        $includeSampleCode = $request->boolean('includeSampleCode') ? '-with-sample-code' : '';
        $stub = resource_path("stubs/indicators/{$request->chosen_chart_type}{$includeSampleCode}.stub");

        $attributes = new IndicatorAttributes(
            name: $validated['name'],
            title: $validated['title'],
            dataSource: $validated['data_source'],
            type: $request->chosen_chart_type,
            description: $validated['description'] ?? null,
            data: $selectedTemplate?->data ?? [],
            layout: $selectedTemplate?->layout ?? self::DEFAULT_LAYOUT,
            stub: $stub,
        );
        $result = $createArtefactAction->execute(modelClass: Indicator::class, baseNamespace: '\Livewire\Indicator', attributes: $attributes);
        if ($result->success) {
            return redirect()->route('indicator.index')->withMessage('Indicator created');
        }

        return redirect()->route('indicator.index')->withErrors('There was a problem creating the indicator.');
    }
}
