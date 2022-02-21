<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageRequest;
use App\Models\Questionnaire;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $records = Page::withCount('indicators')->orderBy('title')->get();
        return view('page.index', compact('records'));
    }

    public function create()
    {
        $connections = Questionnaire::pluck('title', 'id');
        return view('page.create', compact('connections'));
    }

    public function store(PageRequest $request)
    {
        //$request = $request->merge(['slug' => Str::of($request->get('title'))->slug('-')]);
        Page::create($request->only(['title',  'description', 'connection']));
        return redirect()->route('page.index')->withMessage('Page created');
    }

    public function edit(Page $page)
    {
        $connections = Questionnaire::pluck('name', 'id');
        return view('page.edit', compact('page', 'connections'));
    }

    public function update(Page $page, Request $request)
    {
        //$request = $request->merge(['slug' => Str::of($request->get('title'))->slug('-')]);
        $page->update($request->only(['title', 'description', 'connection']));
        return redirect()->route('page.index')->withMessage('Page updated');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('page.index')->withMessage('Page deleted');
    }
}
