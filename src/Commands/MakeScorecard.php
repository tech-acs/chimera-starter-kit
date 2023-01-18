<?php

namespace Uneca\Chimera\Commands;

use Spatie\Permission\Models\Permission;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Traits\InteractiveCommand;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;

class MakeScorecard extends GeneratorCommand
{
    protected $signature = 'chimera:make-scorecard';

    protected $description = 'Create a new scorecard component. Creates file from stub and adds entry in scorecards table.';

    protected $type = 'default';

    use InteractiveCommand;

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Livewire\Scorecard';
    }

    protected function getStub()
    {
        return resource_path("stubs/scorecards/{$this->type}.stub");
    }

    protected function writeFile(string $name)
    {
        $className = $this->qualifyClass($name);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        $content = $this->buildClass($className);
        return $this->files->put($path, $content);
    }

    private function ensureScorecardsPermissionExists()
    {
        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'scorecards']);
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
            "Please provide a name for the scorecard\n\n (This will serve as the component name and has to be in camel case. Eg. TotalHouseholds\n You can also include directory to help with organization of scorecard files. Eg. Household/BirthRate)",
            'name',
            ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:scorecards,name']
        );

        $questionnaires = Questionnaire::pluck('name')->toArray();
        $questionnaireMenu = array_combine(range(1, count($questionnaires)), array_values($questionnaires));
        $questionnaire = $this->choice("Which questionnaire does this indicator belong to?", $questionnaireMenu);

        $title = $this->askValid(
            "Please enter a reader friendly title for the indicator (press enter to leave empty for now)",
            'title',
            ['nullable', ]
        );

        $this->ensureScorecardsPermissionExists();

        DB::transaction(function () use ($name, $title, $questionnaire) {

            $result = $this->writeFile($name);
            if ($result) {
                $this->info('Scorecard created successfully.');
            } else {
                throw new \Exception('There was a problem creating the class file');
            }

            Scorecard::create([
                'name' => $name,
                'title' => $title,
                'questionnaire' => $questionnaire,
            ]);
        });

        return 0;
    }
}
