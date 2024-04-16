<?php

namespace Uneca\Chimera\Commands;

use Spatie\Permission\Models\Permission;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Scorecard;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeScorecard extends GeneratorCommand
{
    protected $signature = 'chimera:make-scorecard';

    protected $description = 'Create a new scorecard component. Creates file from stub and adds entry in scorecards table.';

    protected $type = 'default';

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
        if (DataSource::all()->isEmpty()) {
            error("You have not yet added questionnaires to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        $name = text(
            label: "Scorecard name (this will be the component name and has to be in camel case)",
            placeholder: 'Household/BirthRate',
            hint: "Eg. TotalHouseholds or Household/BirthRate (including directory helps organize indicator files)",
            validate: ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:scorecards,name']
        );
        $questionnaires = DataSource::pluck('name')->toArray();
        $questionnaireMenu = array_combine(range(1, count($questionnaires)), array_values($questionnaires));
        $questionnaire = select("Which questionnaire does this scorecard belong to?", $questionnaireMenu);
        $title = text(
            label: "Please enter a reader friendly title for the scorecard (press enter to leave empty for now) ",
            validate: ['nullable',]
        );
        $this->ensureScorecardsPermissionExists();

        DB::transaction(function () use ($name, $title, $questionnaire) {
            $result = $this->writeFile($name);
            if ($result) {
                info('Scorecard created successfully.');
            } else {
                throw new \Exception('There was a problem creating the class file');
            }
            Scorecard::create([
                'name' => $name,
                'title' => $title,
                'questionnaire' => $questionnaire,
            ]);
        });

        return self::SUCCESS;
    }
}
