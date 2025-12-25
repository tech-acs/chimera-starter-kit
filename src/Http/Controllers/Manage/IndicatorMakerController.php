<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateIndicatorAction;
use App\Http\Controllers\Controller;
use Uneca\Chimera\DTOs\IndicatorAttributes;
use Uneca\Chimera\Http\Requests\IndicatorMakerRequest;
use Uneca\Chimera\Models\ChartTemplate;
use Uneca\Chimera\Models\DataSource;
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

    public function store(IndicatorMakerRequest $request, CreateIndicatorAction $createIndicatorAction)
    {
        $selectedTemplate = ChartTemplate::find($request->selectedTemplateId ?? null);
        $includeSampleCode = $request->boolean('includeSampleCode') ? '-with-sample-code' : '';
        $stub = resource_path("stubs/indicators/{$request->chosen_chart_type}{$includeSampleCode}.stub");

        $indicatorAttributes = new IndicatorAttributes(
            name: $request->indicator_name,
            title: $request->title,
            dataSource: $request->data_source,
            type: $request->chosen_chart_type,
            description: $request->description,
            data: $selectedTemplate?->data ?? [],
            layout: $selectedTemplate?->layout ?? self::DEFAULT_LAYOUT,
            stub: $stub,
        );
        try {
            $createIndicatorAction->execute($indicatorAttributes);
            return redirect()->route('indicator.index')->withMessage('Indicator created');

        } catch (\Exception) {
            return redirect()->route('indicator.index')->withErrors('There was a problem creating the indicator.');
        }
    }
}
