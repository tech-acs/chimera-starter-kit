<?php

namespace Uneca\Chimera\Commands;

use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\DataSource;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use function Laravel\Prompts\select;
use function Laravel\Prompts\error;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\textarea;

class MakeMapIndicator extends GeneratorCommand
{
    protected $signature = 'chimera:make-map-indicator';
    protected $description = 'Create a new map indicator. Creates file from stub and adds entry in map_indicators table.';

    protected $type = 'default';

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
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        $name = text(
            label: "Map indicator name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:map_indicators,name']],
            hint: "This will serve as the component name and has to be in camel case"
        );
        $dataSource = select(
            label: "Which data source will this map indicator be using?",
            options: $dataSources->pluck('name', 'name')->toArray(),
            hint: "You will not be able to change this later"
        );
        $title = text(
            label: "Please enter a reader friendly title for the map indicator",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
        );
        $description = textarea(
            label: "Please enter a description for the map indicator",
            placeholder: "E.g. This map indicator displays on a map by using RAG colors, the percentage of work completed",
            hint: "You can leave this empty for now"
        );
        $this->ensureMapIndicatorsPermissionExists();

        $mapIndicator = MapIndicator::create([
            'name' => $name,
            'title' => $title,
            'description' => $description,
            'data_source' => $dataSource,
        ]);
        DB::transaction(function () use ($mapIndicator, $name) {
            if ($this->writeFile($name)) {
                info('Map indicator created successfully.');
            } else {
                throw new \Exception('There was a problem creating the class file');
            }
            $mapIndicator->save();
        });

        return self::SUCCESS;
    }
}
