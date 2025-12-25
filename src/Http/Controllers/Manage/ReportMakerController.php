<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Actions\Maker\CreateReportAction;
use App\Http\Controllers\Controller;
use Uneca\Chimera\DTOs\ReportAttributes;
use Uneca\Chimera\Http\Requests\ReportMakerRequest;
use Uneca\Chimera\Models\DataSource;

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

    public function store(ReportMakerRequest $request, CreateReportAction $createReportAction)
    {
        $reportAttributes = new ReportAttributes(
            name: $request->report_name,
            title: $request->title,
            description: $request->description,
            dataSource: $request->data_source,
            stub: resource_path("stubs/reports/default.stub")
        );
        try {
            $createReportAction->execute($reportAttributes);
            return redirect()->route('manage.report.index')->withMessage('Report created');

        } catch (\Exception) {
            return redirect()->route('manage.report.index')->withErrors('There was a problem creating the report.');
        }
    }
}
