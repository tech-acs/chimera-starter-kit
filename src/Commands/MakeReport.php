<?php

namespace Uneca\Chimera\Commands;

use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Report;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\textarea;

class MakeReport extends GeneratorCommand
{
    protected $signature = 'chimera:make-report';

    protected $description = 'Create a new report. Creates file from stub and adds entry in reports table.';

    protected $type = 'default';

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Reports';
    }

    protected function getStub()
    {
        return resource_path("stubs/reports/{$this->type}.stub");
    }

    protected function writeFile(string $name)
    {
        $className = $this->qualifyClass($name);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        $content = $this->buildClass($className);
        return $this->files->put($path, $content);
    }

    private function ensureReportsPermissionExists()
    {
        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'reports']);
    }

    public function handle()
    {
        if (DataSource::all()->isEmpty()) {
            error("You have not yet added questionnaires to your dashboard. Please do so first.");
            return self::FAILURE;
        }
        $name = text(
            label: "Report name (this will be the component name and has to be in camel case)",
            placeholder: 'Household/BirthRate',
            hint: "Eg. HouseholdsEnumeratedByDay or Household/BirthRate (including directory helps organize indicator files)",
            validate: ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:reports,name']
        );
        $questionnaires = DataSource::pluck('name')->toArray();
        $questionnaireMenu = array_combine(range(1, count($questionnaires)), array_values($questionnaires));

        $questionnaire = select("Which questionnaire does this report belong to?", $questionnaireMenu);
        $title = text(
            label: "Please enter a reader friendly title for the report (press enter to leave empty for now) ",
            validate: ['nullable',]
        );
        $description = textarea(
            label: "Please enter a description for the report (press enter to leave empty for now)",
            validate: ['nullable',]
        );
        $this->ensureReportsPermissionExists();

        DB::transaction(function () use ($name, $title, $description, $questionnaire) {
            $result = $this->writeFile($name);
            if ($result) {
                info('Report created successfully.');
            } else {
                throw new \Exception('There was a problem creating the report file');
            }

            Report::create([
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'questionnaire' => $questionnaire,
            ]);
        });

        return self::SUCCESS;
    }
}
