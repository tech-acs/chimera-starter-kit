<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Routing\Controller;
use Uneca\Chimera\DTOs\MapIndicatorAttributes;
use Uneca\Chimera\Http\Requests\MapIndicatorMakerRequest;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\MapIndicator;

class MapIndicatorMakerController extends Controller
{
    public function create()
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            return redirect()->route('manage.map_indicator.index')
                ->withMessage('You have not yet added data sources to your dashboard. Please do so first.');
        }

        return view('chimera::map_indicator.create', [
            'dataSources' => $dataSources->pluck('title', 'name')->toArray(),
        ]);
    }

    public function store(MapIndicatorMakerRequest $request, CreateArtefactAction $createArtefactAction)
    {
        $validated = $request->validated();
        $attributes = new MapIndicatorAttributes(
            name: $validated['name'],
            title: $validated['title'],
            description: $validated['description'] ?? null,
            dataSource: $validated['data_source'],
            stub: resource_path('stubs/map_indicators/default.stub')
        );
        $result = $createArtefactAction->execute(modelClass: MapIndicator::class, baseNamespace: '\MapIndicators', attributes: $attributes);
        if ($result->success) {
            return redirect()->route('manage.map_indicator.index')->withMessage('Map indicator created');
        }

        return redirect()->route('manage.map_indicator.index')->withErrors('There was a problem creating the map indicator.');
    }
}
