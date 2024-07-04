<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\GeneratorCommand;
use Uneca\Chimera\Models\DataSource;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class MakeQueryFragment extends GeneratorCommand
{
    protected $signature = 'chimera:make-queryfragment {--data-source=}';
    protected $description = 'Create a query fragment service class for the given data source.';

    protected $dataSource;

    protected function getStub()
    {
        return resource_path("stubs/QueryFragment.php.stub");
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Services\\QueryFragments';
    }

    protected function writeFile(string $filename)
    {
        $className = $this->qualifyClass($filename);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        $content = $this->buildClass($className);
        return $this->files->put($path, $content);
    }

    public function handle()
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        if ($this->option('data-source')) {
            $dataSource = $this->option('data-source');
        } else {
            $dataSource = select(
                label: "Which data source are you creating this query fragment for?",
                options: $dataSources->pluck('title', 'name')->toArray(),
                hint: "You will not be able to change this later"
            );
        }

        $filename = str($dataSource)->studly() . "QueryFragments";

        if ($this->writeFile($filename)) {
            info("$filename class created successfully.");
        } else {
            throw new \Exception('There was a problem creating the class file');
        }

        return self::SUCCESS;
    }
}
