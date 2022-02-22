<?php

namespace App\Console\Commands;

use App\Models\Indicator;
use App\Models\Questionnaire;
use App\Services\Traits\InteractiveCommand;
use Illuminate\Console\Command;
use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeIndicator extends GeneratorCommand
{
    protected $signature = 'chimera:make-indicator';

    protected $description = 'Command description';

    //protected $files;

    use InteractiveCommand;

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Livewire';
    }

    protected function getStub()
    {
        return resource_path('stubs/indicator.stub');
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
            ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/i', 'unique:indicators,name']
        );

        $questionnaires = Questionnaire::pluck('name')->all();
        $questionnaireMenu = array_combine(range(1, count($questionnaires)), array_values($questionnaires));
        $questionnaire = $this->choice("Which questionnaire does this indicator belong to?", $questionnaireMenu);

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

        Indicator::create([
            'name' => $name,
            'title' => $title,
            'description' => $description,
            'questionnaire' => $questionnaire,
        ]);

        $name = str($name)->afterLast('/');
        $className = $this->qualifyClass($name);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($name));
        $this->info('Indicator created successfully.');

        //dump($className, $path);
        return 0;
    }
}
