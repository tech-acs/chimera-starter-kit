<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Uneca\Chimera\Http\Requests\MapIndicatorRequest;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Services\SmartTableColumn;
use Uneca\Chimera\Services\SmartTableData;

class MapIndicatorController extends Controller
{
    public function index(Request $request)
    {
        return (new SmartTableData(MapIndicator::query(), $request))
            ->columns([
                SmartTableColumn::make('title')
                    ->sortable()
                    ->setBladeTemplate('<div>{{ $row->title }}</div><div class="text-xs text-gray-400">{{ $row->name }}</div>'),
                SmartTableColumn::make('data_source')
                    ->setLabel('Data Source')
                    ->sortable()
                    ->setBladeTemplate('{{ $row->getDataSource()->title }}'),
                SmartTableColumn::make('published')
                    ->setBladeTemplate('<x-chimera::yes-no value="{{ $row->published }}" />'),
            ])
            ->editable('manage.map_indicator.edit')
            ->searchable(['title', 'name', 'data_source'])
            ->sortBy('title')
            ->downloadable()
            ->view('chimera::map_indicator.index');
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
