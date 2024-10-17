<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Report;
use Uneca\Chimera\Services\DashboardComponentFactory;

class ReportManagementRunNowController extends Controller
{
    public function __invoke(Report $report)
    {
        dispatch(function () use ($report) {
            $report = DashboardComponentFactory::makeReport($report);
            $report->generate();
        });

        /*$report = DashboardComponentFactory::makeReport($report);
        $report->generate();*/

        return redirect()->route('manage.report.index')
            ->withMessage('The report is being generated. Please check back later.');
    }
}
