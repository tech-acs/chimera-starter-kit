<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Http\Requests\StatRequest;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Scorecard;

class ScorecardController extends Controller
{
    public function index()
    {
        $records = Scorecard::orderBy('rank')->get();
        return view('chimera::scorecard.index', compact('records'));
    }

    public function edit(Scorecard $scorecard)
    {
        $indicators = Indicator::where('questionnaire', $scorecard->questionnaire)->get()->pluck('title', 'slug');
        return view('chimera::scorecard.edit', compact('scorecard', 'indicators'));
    }

    public function update(Scorecard $scorecard, StatRequest $request)
    {
        $scorecard->update($request->only(['title', 'description', 'linked_indicator', 'published', 'rank']));
        return redirect()->route('scorecard.index')->withMessage('Record updated');
    }
}
