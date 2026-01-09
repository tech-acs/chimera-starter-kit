<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Uneca\Chimera\Http\Requests\ScorecardRequest;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\SmartTableColumn;
use Uneca\Chimera\Services\SmartTableData;

class ScorecardController extends Controller
{
    public function index(Request $request)
    {
        return (new SmartTableData(Scorecard::query(), $request))
            ->columns([
                SmartTableColumn::make('title')
                    ->sortable()
                    ->setBladeTemplate('<div>{{ $row->title }} <x-chimera::icon.linked class="text-blue-600" :value="$row->linked_indicator" /></div><div class="text-xs text-gray-400">{{ $row->name }}</div>'),
                SmartTableColumn::make('data_source')
                    ->setLabel('Data Source')
                    ->sortable()
                    ->setBladeTemplate('{{ $row->getDataSource()->title }}'),
                SmartTableColumn::make('scope')
                    ->sortable(),
                SmartTableColumn::make('published')
                    ->setBladeTemplate('<x-chimera::yes-no value="{{ $row->published }}" />'),
            ])
            ->editable('scorecard.edit')
            ->searchable(['title', 'name', 'data_source'])
            ->sortBy('title')
            ->downloadable()
            ->view('chimera::scorecard.index');
    }

    public function edit(Scorecard $scorecard)
    {
        $areaHierarchies = AreaHierarchy::orderBy('index')->pluck('name', 'id')->all();
        $indicators = Indicator::where('data_source', $scorecard->data_source)->get()->pluck('title', 'slug');
        return view('chimera::scorecard.edit', compact('scorecard', 'indicators', 'areaHierarchies'));
    }

    public function update(Scorecard $scorecard, ScorecardRequest $request)
    {
        $scorecard->update($request->only(['title', 'description', 'linked_indicator', 'published', 'rank', 'scope']));
        $scorecard->inapplicableLevels()->sync($request->get('inapplicable_levels', []));
        return redirect()->route('scorecard.index')->withMessage('Record updated');
    }
}
