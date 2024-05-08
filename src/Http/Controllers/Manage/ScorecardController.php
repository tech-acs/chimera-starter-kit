<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Uneca\Chimera\Http\Requests\StatRequest;
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
                    ->setBladeTemplate('<div>{{ $row->title }}</div><div class="text-xs text-gray-400">{{ $row->name }}</div>'),
                SmartTableColumn::make('data_source')
                    ->setLabel('Data Source')
                    ->sortable()
                    ->setBladeTemplate('{{ $row->getDataSource()->title }}'),
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
        $indicators = Indicator::where('data_source', $scorecard->dataSource)->get()->pluck('title', 'slug');
        return view('chimera::scorecard.edit', compact('scorecard', 'indicators'));
    }

    public function update(Scorecard $scorecard, StatRequest $request)
    {
        $scorecard->update($request->only(['title', 'description', 'linked_indicator', 'published', 'rank']));
        return redirect()->route('scorecard.index')->withMessage('Record updated');
    }
}
