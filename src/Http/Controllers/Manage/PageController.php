<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Enums\PageableTypes;
use Uneca\Chimera\Http\Requests\PageRequest;
use Uneca\Chimera\Models\Page;
use Illuminate\Support\MessageBag;

class PageController extends Controller
{
    public function index()
    {
        $groupedPages = Page::includeArtefactCount()
            ->orderBy('for')
            ->orderBy('rank')
            ->get()
            ->groupBy('for');
        return view('chimera::page.index', compact('groupedPages'));
    }

    public function create()
    {
        return view('chimera::page.create', ['pageableTypes' => PageableTypes::cases(), 'page' => new Page()]);
    }

    public function store(PageRequest $request)
    {
        Page::create($request->only(['title',  'description', 'for', 'published', 'rank']));
        return redirect()->route('page.index')->withMessage('Page created');
    }

    public function edit(Page $page)
    {
        $page->load(['indicators' => function ($query) {
            $query->where('published', true);
        }]);
        $pageableTypes = PageableTypes::cases();
        return view('chimera::page.edit', compact('page', 'pageableTypes'));
    }

    public function update(Page $page, PageRequest $request)
    {
        $page->update($request->only(['title', 'description', 'for', 'published', 'rank']));
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
