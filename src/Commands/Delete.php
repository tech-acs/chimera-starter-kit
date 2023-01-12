<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Uneca\Chimera\Traits\InteractiveCommand;

class Delete extends Command
{
    protected $signature = 'chimera:delete';

    protected $description = 'Delete dashboard elements such as indicator, report etc. Removes file and database entry.';

    protected array $elementsMenu = [
        'Indicator' => "App\Http\Livewire",
        'Scorecard' => "App\Http\Livewire\Scorecard",
        'Report' => "App\Reports",
        'MapIndicator' => "App\MapIndicators",
    ];

    use InteractiveCommand;

    public function handle()
    {
        $menu = array_combine(range(1, count($this->elementsMenu)), array_keys($this->elementsMenu));
        $chosenElement = $this->choice("What do you want to delete?", $menu);
        $class = "Uneca\Chimera\Models\\" . $chosenElement;
        $list = app($class)
            ->all()
            ->mapWithKeys(function ($item) {
                return [$item->name => $item->id];
            })->all();
        $listMenu = array_keys($list);
        array_unshift($listMenu, '');
        unset($listMenu[0]);

        if (empty($listMenu)) {
            $this->info("No {$chosenElement}s found!");
            $this->newLine();
        } else {
            $chosenRecord = $this->choice("Which $chosenElement do you want to delete?", $listMenu);
            $recordId = $list[$chosenRecord];
            $modelToDelete = $class::find($recordId);

            $namespace = $this->elementsMenu[$chosenElement];
            if (empty($namespace)) {
                $modelToDelete->delete();
            } else {
                $reflection = new \ReflectionClass($namespace . '\\' . str_replace('/', '\\', $modelToDelete->name));
                $pathToFile = $reflection->getFileName();
                if ($pathToFile) {
                    unlink($pathToFile);
                    $modelToDelete->delete();
                    Artisan::call('permission:cache-reset');
                }
            }
            $this->info("Successfully deleted");
        }
        return 0;
    }
}
