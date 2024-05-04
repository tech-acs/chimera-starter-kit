<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Uneca\Chimera\Http\Requests\IndicatorRequest;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Page;
use Uneca\Chimera\Services\SmartTableColumn;
use Uneca\Chimera\Services\SmartTableData;

class IndicatorController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Indicator::with('pages');
        $smartTableData = (new SmartTableData($baseQuery, $request))
            ->columns([
                SmartTableColumn::make('name')
                    ->sortable(),
                SmartTableColumn::make('data_source')
                    ->setLabel('Data Source')
                    ->sortable()
                    ->setBladeTemplate('{{ $row->getDataSource()->title }}'),
                SmartTableColumn::make('tag')
                    ->setBladeTemplate('{{ $row->tag ?? "-" }}'),
                SmartTableColumn::make('pages.title')->setLabel('Page')
                    ->setBladeTemplate('{{ $row->pages->isEmpty() ? "Not assigned" : $row->pages->pluck("title")->join(", ") }}'),
                SmartTableColumn::make('published')
                    ->setBladeTemplate('<x-chimera::yes-no value="{{ $row->published }}" />'),
            ])
            ->searchable(['name', 'data_source'])
            ->sortBy('name')
            ->downloadable()
            ->build();
        return view('chimera::indicator.index', compact('smartTableData'));
    }

    public function edit(Indicator $indicator)
    {
        $pages = Page::pluck('title', 'id');
        $tags = config('chimera.cache.tags', []);
        return view('chimera::indicator.edit', compact('indicator', 'pages', 'tags'));
    }

    public function update(Indicator $indicator, IndicatorRequest $request)
    {
        $indicator->pages()->sync($request->get('pages', []));
        $indicator->update($request->only(['title', 'description', 'help', 'published', 'tag']));
        return redirect()->route('indicator.index')->withMessage('Record updated');
    }
}
