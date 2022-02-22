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
        $questionnaires = Questionnaire::pluck('title', 'id');
        return view('page.create', compact('questionnaires'));
    }

    public function store(PageRequest $request)
    {
        Page::create($request->only(['title',  'description', 'questionnaire']));
        return redirect()->route('page.index')->withMessage('Page created');
    }

    public function edit(Page $page)
    {
        $questionnaires = Questionnaire::pluck('name', 'id');
        return view('page.edit', compact('page', 'questionnaires'));
    }

    public function update(Page $page, Request $request)
    {
        $page->update($request->only(['title', 'description', 'questionnaire']));
        return redirect()->route('page.index')->withMessage('Page updated');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('page.index')->withMessage('Page deleted');
    }
}
