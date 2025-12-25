<?php

namespace App\Actions\Maker;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command;
use Uneca\Chimera\DTOs\MapIndicatorAttributes;
use Uneca\Chimera\Models\MapIndicator;

class CreateMapIndicatorAction
{
    public function execute(MapIndicatorAttributes $mapIndicatorAttributes): void
    {
        DB::transaction(function () use ($mapIndicatorAttributes) {
            MapIndicator::create($mapIndicatorAttributes->toArray());

            $exitCode = Artisan::call('chimera:make-artefact', [
                'name' => $mapIndicatorAttributes->name,
                '--stub' => $mapIndicatorAttributes->stub,
                '--namespace' => '\MapIndicators',
            ]);
            if ($exitCode !== Command::SUCCESS) {
                throw new Exception('There was a problem creating the class file');
            }
        });
    }
}
