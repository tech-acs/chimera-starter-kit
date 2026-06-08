<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Routing\Controller;
use Uneca\Chimera\DTOs\ReportAttributes;
use Uneca\Chimera\Http\Requests\ReportMakerRequest;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Report;

class ReportMakerController extends Controller
{
    public function create()
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            return redirect()->route('manage.report.index')
                ->withMessage('You have not yet added data sources to your dashboard. Please do so first.');
        }

        return view('chimera::report.manage.create', [
            'dataSources' => $dataSources->pluck('title', 'name')->toArray(),
        ]);
    }

    public function store(ReportMakerRequest $request, CreateArtefactAction $createArtefactAction)
    {
        $validated = $request->validated();
        $attributes = new ReportAttributes(
            name: $validated['name'],
            title: $validated['title'],
            description: $validated['description'] ?? null,
            dataSource: $validated['data_source'],
            stub: resource_path('stubs/reports/default.stub')
        );
        $result = $createArtefactAction->execute(modelClass: Report::class, baseNamespace: '\Reports', attributes: $attributes);
        if ($result->success) {
            return redirect()->route('manage.report.index')->withMessage('Report created');
        }

        return redirect()->route('manage.report.index')->withErrors('There was a problem creating the report.');
    }
}
