<?php

namespace App\Console\Commands;

use App\Models\Report;
use Illuminate\Console\Command;

class GenerateReports extends Command
{
    protected $signature = 'chimera:generate-reports';

    protected $description = 'Generate (output) all reports scheduled for the current time';

    public function handle()
    {
        $dueReports = Report::enabled()->dueThisHour();
        foreach ($dueReports as $report) {
            $report->blueprintInstance->generate();
        }
        return 0;
    }
}
