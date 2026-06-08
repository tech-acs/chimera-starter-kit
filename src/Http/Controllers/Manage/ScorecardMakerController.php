<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Routing\Controller;
use Uneca\Chimera\DTOs\ScorecardAttributes;
use Uneca\Chimera\Http\Requests\ScorecardMakerRequest;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Scorecard;

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

    public function store(ScorecardMakerRequest $request, CreateArtefactAction $createArtefactAction)
    {
        $validated = $request->validated();
        $scorecardAttributes = new ScorecardAttributes(
            name: $validated['name'],
            title: $validated['title'],
            dataSource: $validated['data_source'],
            stub: resource_path('stubs/scorecards/default.stub')
        );
        $result = $createArtefactAction->execute(modelClass: Scorecard::class, baseNamespace: 'Livewire\Scorecard', attributes: $scorecardAttributes);
        if ($result->success) {
            return redirect()->route('scorecard.index')->withMessage('Scorecard created');
        }
        return redirect()->route('scorecard.index')->withErrors('There was a problem creating the scorecard.');
    }
}
