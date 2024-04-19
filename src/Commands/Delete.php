<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;

class Delete extends Command
{
    protected $signature = 'chimera:delete';

    protected $description = 'Delete dashboard elements such as indicator, report etc. Removes file and database entry.';

    public function handle()
    {
        $modelTypeMenu = [
            'Indicator' => "App\Livewire",
            'Scorecard' => "App\Livewire\Scorecard",
            'Report' => "App\Reports",
            'MapIndicator' => "App\MapIndicators",
        ];
        $chosenModelType = select('What do you want to delete?', array_keys($modelTypeMenu));
        $class = "Uneca\Chimera\Models\\" . $chosenModelType;
        $models = app($class)
            ->all()
            ->mapWithKeys(function ($item) {
                return [$item->name => $item->id];
            })->all();

        if (empty($models)) {
            info("No " . str($chosenModelType)->plural() . " found!");
        } else {
            $chosenModel = select("Which $chosenModelType do you want to delete?", array_keys($models));
            $modelToDelete = $class::find($models[$chosenModel]);
            $namespace = $modelTypeMenu[$chosenModelType];
            if (empty($namespace)) {
                $modelToDelete->delete();
            } else {
                $reflection = new \ReflectionClass($namespace . '\\' . str_replace('/', '\\', $modelToDelete->name));
                $pathToFile = $reflection->getFileName();
                if ($pathToFile) {
                    $result = unlink($pathToFile);
                    $modelToDelete->delete();
                    $this->callSilently('permission:cache-reset');
                }
            }
            info("Successfully deleted");
        }
        return self::SUCCESS;
    }
}
