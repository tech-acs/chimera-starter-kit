<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatRequest;
use App\Models\Scorecard;

class ScorecardController extends Controller
{
    public function index()
    {
        $records = Scorecard::orderBy('title')->get();
        return view('scorecard.index', compact('records'));
    }

    public function edit(Scorecard $scorecard)
    {
        return view('scorecard.edit', compact('scorecard'));
    }

    public function update(Scorecard $scorecard, StatRequest $request)
    {
        $scorecard->update($request->only(['title', 'description', 'published']));
        return redirect()->route('scorecard.index')->withMessage('Record updated');
    }
}
