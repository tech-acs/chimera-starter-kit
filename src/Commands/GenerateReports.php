<?php

namespace Uneca\Chimera\Commands;

use Uneca\Chimera\Models\Report;
use Illuminate\Console\Command;
use Uneca\Chimera\Services\DashboardComponentFactory;

class GenerateReports extends Command
{
    protected $signature = 'chimera:generate-reports';

    protected $description = 'Generate (output) all reports scheduled for the current time';

    public function handle()
    {
        $dueReports = Report::enabled()
            ->get()
            ->filter(function ($report) {
                return in_array(now()->format('H:00:00'), $report->schedule());
            });
        foreach ($dueReports as $report) {
            $implementedReport = DashboardComponentFactory::makeReport($report);
            $implementedReport->generate();
        }
        return 0;
    }
}
