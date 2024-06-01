<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
        return (new SmartTableData(Indicator::with('pages'), $request))
            ->columns([
                SmartTableColumn::make('title')
                    ->sortable()
                    ->setBladeTemplate('<div>{{ $row->title }} <x-chimera::icon.featured class="text-amber-600" :value="$row->featured_at" /></div><div class="text-xs text-gray-400">{{ $row->name }}</div>'),
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
            ->searchable(['title', 'name', 'data_source'])
            ->sortBy('title')
            ->downloadable()
            ->view('chimera::indicator.index');
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
        $request->merge(['featured_at' => $request->get('is_featured', false) ? Carbon::now() : null]);
        $indicator->update($request->only(['title', 'description', 'help', 'published', 'tag', 'featured_at']));
        return redirect()->route('indicator.index')->withMessage('Record updated');
    }
}
