<?php

namespace App\Actions\Maker;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command;
use Uneca\Chimera\DTOs\ReportAttributes;
use Uneca\Chimera\Models\Report;


class CreateReportAction
{
    public function execute(ReportAttributes $reportAttributes): void
    {
        DB::transaction(function () use ($reportAttributes) {
            Report::create($reportAttributes->toArray());

            $exitCode = Artisan::call('chimera:make-artefact', [
                'name' => $reportAttributes->name,
                '--stub' => $reportAttributes->stub,
                '--namespace' => '\Reports',
            ]);
            if ($exitCode !== Command::SUCCESS) {
                throw new Exception('There was a problem creating the class file');
            }
        });
    }
}
