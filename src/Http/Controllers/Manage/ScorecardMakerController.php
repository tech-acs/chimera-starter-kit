<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateScorecardAction;
use App\Http\Controllers\Controller;
use Uneca\Chimera\DTOs\ScorecardAttributes;
use Uneca\Chimera\Http\Requests\ScorecardMakerRequest;
use Uneca\Chimera\Models\DataSource;

class ScorecardMakerController extends Controller
{
    public function create()
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            return redirect()->route('scorecard.index')
                ->withMessage('You have not yet added data sources to your dashboard. Please do so first.');
        }
        return view('chimera::scorecard.create', [
            'dataSources' => $dataSources->pluck('title', 'name')->toArray(),
        ]);
    }

    public function store(ScorecardMakerRequest $request, CreateScorecardAction $createScorecardAction)
    {
        $scorecardAttributes = new ScorecardAttributes(
            name: $request->scorecard_name,
            title: $request->title,
            dataSource: $request->data_source,
            stub: resource_path("stubs/scorecards/default.stub")
        );
        try {
            $createScorecardAction->execute($scorecardAttributes);
            return redirect()->route('scorecard.index')->withMessage('Scorecard created');

        } catch (\Exception) {
            return redirect()->route('scorecard.index')->withErrors('There was a problem creating the scorecard.');
        }
    }
}
