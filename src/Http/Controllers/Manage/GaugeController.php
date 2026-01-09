<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Uneca\Chimera\Http\Requests\GaugeRequest;
use Uneca\Chimera\Http\Requests\ScorecardRequest;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\SmartTableColumn;
use Uneca\Chimera\Services\SmartTableData;

class GaugeController extends Controller
{
    public function index(Request $request)
    {
        return (new SmartTableData(Gauge::query(), $request))
            ->columns([
                SmartTableColumn::make('title')->sortable(),
                SmartTableColumn::make('subtitle')->sortable(),
                SmartTableColumn::make('data_source')
                    ->setLabel('Data Source')
                    ->sortable()
                    ->setBladeTemplate('{{ $row->getDataSource()->title }}'),
                SmartTableColumn::make('published')
                    ->setBladeTemplate('<x-chimera::yes-no value="{{ $row->published }}" />'),
            ])
            ->editable('gauge.edit')
            ->searchable(['title', 'subtitle', 'name', 'data_source'])
            ->sortBy('title')
            ->downloadable()
            ->view('chimera::gauge.index');
    }

    public function edit(Gauge $gauge)
    {
        $areaHierarchies = AreaHierarchy::orderBy('index')->pluck('name', 'id')->all();
        $indicators = Indicator::where('data_source', $gauge->data_source)->get()->pluck('title', 'slug');
        return view('chimera::gauge.edit', compact('gauge', 'indicators', 'areaHierarchies'));
    }

    public function update(Gauge $gauge, GaugeRequest $request)
    {
        $gauge->update($request->only(['title', 'subtitle', 'description', 'linked_indicator', 'published', 'rank']));
        $gauge->inapplicableLevels()->sync($request->get('inapplicable_levels', []));
        return redirect()->route('gauge.index')->withMessage('Record updated');
    }
}
