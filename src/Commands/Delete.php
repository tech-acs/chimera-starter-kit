<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\select;
use function Laravel\Prompts\multiselect;
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
            ->mapWithKeys(fn($item) => [$item->id => $item->name])
            ->sort()
            ->all();

        if (empty($models)) {
            info("No " . str($chosenModelType)->plural() . " found!");
        } else {
            $chosenModels = multiselect(
                label: 'Which ' . str($chosenModelType)->plural() . ' do you want to delete?',
                options: $models,
                required: "You must select at least one $chosenModelType",
                hint: 'Use the space bar to select and press enter when done.'
            );
            $modelsToDelete = $class::find($chosenModels);
            $namespace = $modelTypeMenu[$chosenModelType];
            if (empty($namespace)) {
                $modelsToDelete->each->delete();
            } else {
                foreach ($modelsToDelete as $modelToDelete) {
                    $reflection = new \ReflectionClass($namespace . '\\' . str_replace('/', '\\', $modelToDelete->name));
                    $pathToFile = $reflection->getFileName();
                    if ($pathToFile) {
                        $result = unlink($pathToFile);
                        $modelToDelete->delete();
                        $this->callSilently('permission:cache-reset');
                    }
                }
            }
            info("Successfully deleted");
        }
        return self::SUCCESS;
    }
}
