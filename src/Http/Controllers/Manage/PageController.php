<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Http\Requests\PageRequest;
use Uneca\Chimera\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class PageController extends Controller
{
    public function index()
    {
        $records = Page::withCount('indicators')->orderBy('rank')->get();
        return view('chimera::page.index', compact('records'));
    }

    public function create()
    {
        return view('chimera::page.create');
    }

    public function store(PageRequest $request)
    {
        Page::create($request->only(['title',  'description', 'published', 'rank']));
        return redirect()->route('page.index')->withMessage('Page created');
    }

    public function edit(Page $page)
    {
        $page->load(['indicators' => function ($query) {
            $query->where('published', true);
        }]);
        return view('chimera::page.edit', compact('page'));
    }

    public function update(Page $page, PageRequest $request)
    {
        $page->update($request->only(['title', 'description', 'published', 'rank']));
        foreach ($request->get('indicators', []) as $indicatorRank => $indicatorId) {
            $page->indicators()->updateExistingPivot(
                $indicatorId,
                ['rank' => $indicatorRank]
            );
        }
        return redirect()->route('page.index')->withMessage('Page updated');
    }

    public function destroy(Page $page)
    {
        if ($page->indicators()->count() > 0) {
            return redirect()->back()->withErrors(new MessageBag(['The page contains indicators and thus can not be deleted. Move the indicators to another page before trying again.']));
        }
        $page->delete();
        return redirect()->route('page.index')->withMessage('Page deleted');
    }
}
