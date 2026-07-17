<?php

namespace Uneca\Chimera\Actions\Maker;

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
                $filePath = namespaceToPath($baseNamespace, "{$attributes->getName()}.php");

                if (file_exists($filePath)) {
                    throw new Exception("Class file already exists at {$filePath}. Remove it first if you intend to regenerate.");
                }

                $artefact = $modelClass::create($attributes->toArray());

                $exitCode = Artisan::call('chimera:make-artefact', [
                    'name' => $attributes->getName(),
                    '--stub' => $attributes->getStub(),
                    '--namespace' => $baseNamespace,
                ]);
                if ($exitCode !== Command::SUCCESS) {
                    throw new Exception('There was a problem creating the class file');
                }

                return ArtefactCreationResult::success($artefact, $filePath);
            });
        } catch (Exception $e) {
            if (config('chimera.demo_mode')) {
                request()->session()->flash('flash', [
                    'bannerStyle' => 'warning',
                    'banner' => 'This is a demo site. Visitors cannot make changes.',
                ]);
            }

            return ArtefactCreationResult::failed($e->getMessage());
        }
    }
}
