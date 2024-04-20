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
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        $name = text(
            label: "Report name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:reports,name']],
            hint: "This will serve as the component name and has to be in camel case"
        );
        $dataSource = select(
            label: "Which data source will this report be using?",
            options: $dataSources->pluck('title', 'name')->toArray(),
            hint: "You will not be able to change this later"
        );
        $title = text(
            label: "Please enter a reader friendly title for the report",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
        );
        $description = textarea(
            label: "Please enter a description for the report",
            placeholder: "E.g. This reports generates a file showing a list of all enumerators that have not synced their tablets with the server in the last 24 hours",
            hint: "You can leave this empty for now"
        );
        $this->ensureReportsPermissionExists();

        $report = Report::create([
            'name' => $name,
            'title' => $title,
            'description' => $description,
            'data_source' => $dataSource,
        ]);
        DB::transaction(function () use ($report, $name) {
            if ($this->writeFile($name)) {
                info('Report created successfully.');
            } else {
                throw new \Exception('There was a problem creating the class file');
            }
            $report->save();
        });

        return self::SUCCESS;
    }
}
