<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndicatorRequest;
use App\Models\DatabaseConnection;
use App\Models\Indicator;
use App\Models\Page;

class IndicatorController extends Controller
{
    public function index()
    {
        $records = Indicator::with('page')->orderBy('title')->get();
        return view('indicator.index', compact('records'));
    }

    public function edit(Indicator $indicator)
    {
        $connections = DatabaseConnection::pluck('name', 'id');
        $pages = Page::pluck('title', 'id');
        return view('indicator.edit', compact('indicator', 'connections', 'pages'));
    }

    public function update(Indicator $indicator, IndicatorRequest $request)
    {
        $indicator->update($request->only(['title', 'description', 'connection', 'published', 'page_id']));
        return redirect()->route('indicator.index')->withMessage('Record updated');
    }
}
