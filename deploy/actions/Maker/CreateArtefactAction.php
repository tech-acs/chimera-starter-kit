<?php

namespace App\Actions\Maker;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command;
use Uneca\Chimera\Contracts\ArtefactAttributes;
use Uneca\Chimera\Results\ArtefactCreationResult;

class CreateArtefactAction
{
    public function execute(
        string $modelClass,
        string $baseNamespace,
        ArtefactAttributes $attributes,
    ): ArtefactCreationResult {
        try {
            return DB::transaction(function () use ($modelClass, $baseNamespace, $attributes) {
                $artefact = $modelClass::create($attributes->toArray());

                $exitCode = Artisan::call('chimera:make-artefact', [
                    'name' => $attributes->getName(),
                    '--stub' => $attributes->getStub(),
                    '--namespace' => $baseNamespace,
                ]);
                if ($exitCode !== Command::SUCCESS) {
                    throw new Exception('There was a problem creating the class file');
                }

                $filePath = namespaceToPath($baseNamespace, "{$attributes->getName()}.php");

                return ArtefactCreationResult::success($artefact, $filePath);
            });
        } catch (Exception $e) {
            return ArtefactCreationResult::failed($e->getMessage());
        }
    }
}
