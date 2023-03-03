<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Http\Requests\MapIndicatorRequest;
use Uneca\Chimera\Models\MapIndicator;

class MapIndicatorController extends Controller
{
    public function index()
    {
        $records = MapIndicator::orderBy('rank')->get();
        return view('chimera::map_indicator.index', compact('records'));
    }

    public function edit(MapIndicator $mapIndicator)
    {
        return view('chimera::map_indicator.edit', compact('mapIndicator'));
    }

    public function update(MapIndicator $mapIndicator, MapIndicatorRequest $request)
    {
        $mapIndicator->update($request->only(['title', 'description', 'rank', 'published']));
        return redirect()->route('manage.map_indicator.index')->withMessage('Record updated');
    }
}
