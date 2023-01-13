<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Http\Requests\IndicatorRequest;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Page;

class IndicatorController extends Controller
{
    public function index()
    {
        $records = Indicator::with('pages')->orderBy('title')->get();
        return view('chimera::indicator.index', compact('records'));
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
