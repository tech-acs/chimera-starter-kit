<?php

namespace Uneca\Chimera\Commands;

use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Traits\InteractiveCommand;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class MakeMapIndicator extends GeneratorCommand
{
    protected $signature = 'chimera:make-map-indicator';

    protected $description = 'Create a new map indicator. Creates file from stub and adds entry in map_indicators table.';

    protected $type = 'default';

    use InteractiveCommand;

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\MapIndicators';
    }

    protected function getStub()
    {
        return resource_path("stubs/map_indicators/{$this->type}.stub");
    }

    protected function writeFile(string $name)
    {
        $className = $this->qualifyClass($name);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        $content = $this->buildClass($className);
        return $this->files->put($path, $content);
    }

    private function ensureMapIndicatorsPermissionExists()
    {
        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'map_indicators']);
    }

    public function handle()
    {
        if (Questionnaire::all()->isEmpty()) {
            $this->newLine();
            $this->error("You have not yet added questionnaires to your dashboard. Please do so first.");
            $this->newLine();
            return 1;
        }

        $name = $this->askValid(
            "Please provide a name for the map indicator\n\n (This will serve as the class name and has to be in camel case. Eg. HouseholdsEnumeratedByDay\n You can also include directory to help with organization of files. Eg. Household/BirthRate)",
            'name',
            ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:map_indicators,name']
        );

        $questionnaires = Questionnaire::pluck('name')->toArray();
        $questionnaireMenu = array_combine(range(1, count($questionnaires)), array_values($questionnaires));
        $questionnaire = $this->choice("Which questionnaire does this map indicator belong to?", $questionnaireMenu);

        /*$chartTypeMenu = array_combine(range(1, count($this->chartTypes)), array_keys($this->chartTypes));
        $chosenChartType = $this->choice("Please choose the type of chart you want for this indicator", $chartTypeMenu);
        $this->type = $this->chartTypes[$chosenChartType];*/

        $title = $this->askValid(
            "Please enter a reader friendly title for the map indicator (press enter to leave empty for now)",
            'title',
            ['nullable', ]
        );

        $description = $this->askValid(
            "Please enter a description for the map indicator (press enter to leave empty for now)",
            'description',
            ['nullable', ]
        );

        // If the 'reports' (used for controlling 'Reports' page) permission does not already exist, create it!
        $this->ensureMapIndicatorsPermissionExists();

        DB::transaction(function () use ($name, $title, $description, $questionnaire) {

            $result = $this->writeFile($name);
            if ($result) {
                $this->info('Map indicator created successfully.');
            } else {
                throw new \Exception('There was a problem creating the map indicator file');
            }

            MapIndicator::create([
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'questionnaire' => $questionnaire,
            ]);
        });

        return 0;
    }
}
