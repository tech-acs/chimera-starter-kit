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
        return $rootNamespace . '\Livewire\Scorecard';
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
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        $name = text(
            label: "Scorecard name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:scorecards,name']],
            hint: "This will serve as the component name and has to be in camel case"
        );
        $dataSource = select(
            label: "Which data source will this scorecard be using?",
            options: $dataSources->pluck('name', 'name')->toArray(),
            hint: "You will not be able to change this later"
        );
        $title = text(
            label: "Please enter a reader friendly title for the scorecard",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
        );
        $this->ensureScorecardsPermissionExists();

        $scorecard = Scorecard::make([
            'name' => $name,
            'title' => $title,
            'data_source' => $dataSource,
        ]);
        DB::transaction(function () use ($scorecard, $name) {
            if ($this->writeFile($name)) {
                info('Scorecard created successfully.');
            } else {
                throw new \Exception('There was a problem creating the class file');
            }
            $scorecard->save();
        });

        return self::SUCCESS;
    }
}
