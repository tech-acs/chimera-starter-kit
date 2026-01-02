<?php

namespace App\Actions\Maker;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command;
use Uneca\Chimera\DTOs\IndicatorAttributes;
use Uneca\Chimera\Models\Indicator;

class CreateIndicatorAction
{
    public function execute(IndicatorAttributes $indicatorAttributes): void
    {
        DB::transaction(function () use ($indicatorAttributes) {
            Indicator::create($indicatorAttributes->toArray());

            $exitCode = Artisan::call('chimera:make-artefact', [
                'name' => $indicatorAttributes->name,
                '--stub' => $indicatorAttributes->stub,
                '--namespace' => '\Livewire',
            ]);
            if ($exitCode !== Command::SUCCESS) {
                throw new Exception('There was a problem creating the class file');
            }
        });
    }
}
