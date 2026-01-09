<?php

namespace App\Actions\Maker;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command;
use Uneca\Chimera\DTOs\GaugeAttributes;
use Uneca\Chimera\Models\Gauge;

class CreateGaugeAction
{
    public function execute(GaugeAttributes $gaugeAttributes): void
    {
        DB::transaction(function () use ($gaugeAttributes) {
            Gauge::create($gaugeAttributes->toArray());

            $exitCode = Artisan::call('chimera:make-artefact', [
                'name' => $gaugeAttributes->name,
                '--stub' => $gaugeAttributes->stub,
                '--namespace' => '\Livewire\Gauge',
            ]);
            if ($exitCode !== Command::SUCCESS) {
                throw new Exception('There was a problem creating the class file');
            }
        });
    }
}
