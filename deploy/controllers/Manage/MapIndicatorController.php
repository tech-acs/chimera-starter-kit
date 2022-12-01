<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\MapIndicatorRequest;
use App\Models\MapIndicator;

class MapIndicatorController extends Controller
{
    public function index()
    {
        $records = MapIndicator::orderBy('title')->get();
        return view('map_indicator.index', compact('records'));
    }

    public function edit(MapIndicator $mapIndicator)
    {
        return view('map_indicator.edit', compact('mapIndicator'));
    }

    public function update(MapIndicator $mapIndicator, MapIndicatorRequest $request)
    {
        $mapIndicator->update($request->only(['title', 'description', 'published']));
        return redirect()->route('manage.map_indicator.index')->withMessage('Record updated');
    }
}
