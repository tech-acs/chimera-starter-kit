<?php

namespace App\Console\Commands;

use App\Services\Traits\InteractiveCommand;
use Illuminate\Console\Command;

class Delete extends Command
{
    protected $signature = 'chimera:delete';

    protected $description = 'Delete dashboard elements such as indicator, report etc. Removes file and database entry.';

    protected array $elementsMenu = [1 => 'Indicator', 2 => 'Scorecard', 3 => 'Report'];

    use InteractiveCommand;

    public function handle()
    {
        $chosenElement = $this->choice("What do you want to delete?", $this->elementsMenu);
        $class = "App\Models\\" . $chosenElement;
        $list = app($class)
            ->all()
            ->mapWithKeys(function ($item) {
                return [$item->name => $item->id];
            })->all();
        $listMenu = array_keys($list);
        array_unshift($listMenu, '');
        unset($listMenu[0]);
        $chosenRecord = $this->choice("Which $chosenElement do you want to delete?", $listMenu);
        $recordId = $list[$chosenRecord];
        $modelToDelete = $class::find($recordId);

        // Delete file for this element using $modelToDelete->slug ???
        //unlink("");
        $modelToDelete->delete();
        //$this->info("Successfully deleted");
        return 0;
    }
}
