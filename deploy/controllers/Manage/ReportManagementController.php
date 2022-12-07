<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\Report;

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
        $records = Report::all();
        return view('chimera::report.manage.index', compact('records'));
    }

    public function edit(Report $report)
    {
        $hourOptions = $this->getHourOptions();
        return view('chimera::report.manage.edit', compact('report', 'hourOptions'));
    }

    public function update(ReportRequest $request, Report $report)
    {
        $report->update($request->only(['title', 'description', 'schedule', 'enabled']));
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
