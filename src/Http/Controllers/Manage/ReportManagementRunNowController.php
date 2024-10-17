<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use Uneca\Chimera\Models\Report;
use Uneca\Chimera\Notifications\ReportGeneratedNotification;
use Uneca\Chimera\Services\DashboardComponentFactory;

class ReportManagementRunNowController extends Controller
{
    public function __invoke(Report $report)
    {
        $user = auth()->user();
        dispatch(function () use ($report, $user) {
            $reportArtefact = DashboardComponentFactory::makeReport($report);
            $reportArtefact->generate();
            Notification::send($user, new ReportGeneratedNotification($report));
        });
        return redirect()->route('manage.report.index')
            ->withMessage('The report is being generated. Please check back later.');
    }
}
