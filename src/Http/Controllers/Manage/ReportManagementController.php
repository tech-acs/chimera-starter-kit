<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Http\Requests\ReportRequest;
use Uneca\Chimera\Models\Report;

class ReportManagementController extends Controller
{
    private function getHourOptions()
    {
        return collect()
            ->range(0, 23)
            ->map(fn ($hour) => str($hour)->padLeft(2, '0') . ':00:00');
    }

    public function index()
    {
        $records = Report::orderBy('rank')->get();
        return view('chimera::report.manage.index', compact('records'));
    }

    public function edit(Report $report)
    {
        $hourOptions = $this->getHourOptions();
        $frequencyOptions = [24, 12, 6, 3];
        return view('chimera::report.manage.edit', compact('report', 'hourOptions', 'frequencyOptions'));
    }

    public function update(ReportRequest $request, Report $report)
    {
        //dump($request->all());
        $report->update($request->only(['title', 'description', 'run_at', 'run_every', 'rank', 'enabled', 'published']));
        return redirect()->route('manage.report.index')
            ->withMessage('The report has been updated');
    }

    public function destroy(Report $report)
    {
        $report->delete();
        return redirect()->route('manage.report.index')
            ->withMessage('The report has been deleted');
    }
}
