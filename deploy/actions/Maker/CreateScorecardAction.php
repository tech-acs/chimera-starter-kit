<?php

namespace App\Actions\Maker;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command;
use Uneca\Chimera\DTOs\ScorecardAttributes;
use Uneca\Chimera\Models\Scorecard;;

class CreateScorecardAction
{
    public function execute(ScorecardAttributes $scorecardAttributes): void
    {
        DB::transaction(function () use ($scorecardAttributes) {
            Scorecard::create($scorecardAttributes->toArray());

            $exitCode = Artisan::call('chimera:make-artefact', [
                'name' => $scorecardAttributes->name,
                '--stub' => $scorecardAttributes->stub,
                '--namespace' => '\Livewire\Scorecard',
            ]);
            if ($exitCode !== Command::SUCCESS) {
                throw new Exception('There was a problem creating the class file');
            }
        });
    }
}
