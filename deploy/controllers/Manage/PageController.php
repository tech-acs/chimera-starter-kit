<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class PageController extends Controller
{
    public function index()
    {
        $records = Page::withCount('indicators')->get();
        return view('page.index', compact('records'));
    }

    public function create()
    {
        return view('page.create');
    }

    public function store(PageRequest $request)
    {
        Page::create($request->only(['title',  'description', 'published']));
        return redirect()->route('page.index')->withMessage('Page created');
    }

    public function edit(Page $page)
    {
        $page->load('indicators');
        return view('page.edit', compact('page'));
    }

    public function update(Page $page, Request $request)
    {
        $page->update($request->only(['title', 'description', 'published']));
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
