<?php

namespace App\Console\Commands;

use App\Models\Indicator;
use App\Models\Questionnaire;
use App\Services\Traits\InteractiveCommand;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;

class MakeIndicator extends GeneratorCommand
{
    protected $signature = 'chimera:make-indicator';
    protected $description = 'Create a new indicator component. Creates file from stub and adds entry in indicators table.';

    protected $chartTypes = [
        'Bar chart' => 'barchart',
        'Line chart' => 'linechart',
        'Pie chart' => 'piechart',
        'Default' => 'default',
    ];
    protected $type = 'default';
    protected $includeSampleCode = '';

    use InteractiveCommand;

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Livewire';
    }

    protected function getStub()
    {
        return resource_path("stubs/indicators/{$this->type}{$this->includeSampleCode}.stub");
    }

    protected function writeIndicatorFile(string $name)
    {
        $className = $this->qualifyClass($name);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        $content = $this->buildClass($className);
        return $this->files->put($path, $content);
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
            "Please provide a name for the indicator\n\n (This will serve as the component name and has to be in camel case. Eg. HouseholdsEnumeratedByDay\n You can also include directory to help with organization of indicator files. Eg. Household/BirthRate)",
            'name',
            ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:indicators,name']
        );

        $questionnaires = Questionnaire::pluck('name')->toArray();
        $questionnaireMenu = array_combine(range(1, count($questionnaires)), array_values($questionnaires));
        $questionnaire = $this->choice("Which questionnaire does this indicator belong to?", $questionnaireMenu);

        $chartTypeMenu = array_combine(range(1, count($this->chartTypes)), array_keys($this->chartTypes));
        $chosenChartType = $this->choice("Please choose the type of chart you want for this indicator", $chartTypeMenu);
        $this->type = $this->chartTypes[$chosenChartType];

        $choice = $this->choice("Do you want the generated file to include functioning sample code?", [1 => 'yes', 2 => 'no'], 1);
        $this->includeSampleCode = $choice === 'yes' ? '-with-sample-code' : '';

        $title = $this->askValid(
            "Please enter a reader friendly title for the indicator (press enter to leave empty for now)",
            'title',
            ['nullable', ]
        );

        $description = $this->askValid(
            "Please enter a description for the indicator (press enter to leave empty for now)",
            'description',
            ['nullable', ]
        );

        DB::transaction(function () use ($name, $title, $description, $questionnaire, $chosenChartType) {

            $result = $this->writeIndicatorFile($name);
            if ($result) {
                $this->info('Indicator created successfully.');
            } else {
                throw new \Exception('There was a problem creating the indicator file');
            }

            Indicator::create([
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'questionnaire' => $questionnaire,
                'type' => $chosenChartType,
            ]);
        });

        return 0;
    }
}
