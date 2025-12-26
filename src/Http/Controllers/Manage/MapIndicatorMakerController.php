<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateMapIndicatorAction;
use App\Http\Controllers\Controller;
use Uneca\Chimera\DTOs\MapIndicatorAttributes;
use Uneca\Chimera\Http\Requests\MapIndicatorMakerRequest;
use Uneca\Chimera\Models\DataSource;

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

    public function store(MapIndicatorMakerRequest $request, CreateMapIndicatorAction $createMapIndicatorAction)
    {
        $mapIndicatorAttributes = new MapIndicatorAttributes(
            name: $request->map_indicator_name,
            title: $request->title,
            description: $request->description,
            dataSource: $request->data_source,
            stub: resource_path("stubs/map_indicators/default.stub")
        );
        try {
            $createMapIndicatorAction->execute($mapIndicatorAttributes);
            return redirect()->route('manage.map_indicator.index')->withMessage('Map indicator created');

        } catch (\Exception) {
            return redirect()->route('manage.map_indicator.index')->withErrors('There was a problem creating the scorecard.');
        }
    }
}
