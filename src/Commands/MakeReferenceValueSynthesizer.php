<?php

namespace Uneca\Chimera\Commands;

use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\DataSource;
use Illuminate\Console\GeneratorCommand;
use function Laravel\Prompts\select;
use function Laravel\Prompts\error;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\confirm;

class MakeReferenceValueSynthesizer extends GeneratorCommand
{
    protected $signature = 'chimera:make-reference-value-synthesizer';
    protected $description = 'Create a new reference value synthesizer class.';

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\ReferenceValueSynthesizers';
    }

    protected function getStub()
    {
        return resource_path('stubs/ReferenceValueSynthesizer.php.stub');
    }

    public function replaceStubPlaceholders($contents , $placeholders = [])
    {
        foreach ($placeholders as $search => $replace) {
            $contents = str_replace('{{ ' . $search . ' }}' , $replace, $contents);
        }
        return $contents;
    }

    protected function writeFile(string $name, array $placeholders)
    {
        $className = $this->qualifyClass($name);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        $content = $this->buildClass($className);
        $content = $this->replaceStubPlaceholders($content, $placeholders);
        return $this->files->put($path, $content);
    }

    public function handle()
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }
        $dataSource = select(
            label: "Which data source will the reference values be synthesized from?",
            options: $dataSources->pluck('title', 'name')->toArray(),
            hint: "You will be able to change this later in the class file"
        );
        $indicator = text(
            label: "Please enter a name for the reference values",
            placeholder: 'E.g. population, households, etc.',
            hint: "You can also change this later in the class file",
        );
        $classname = text(
            label: "Reference value synthesizer class name",
            placeholder: 'E.g. PopulationReferenceValue, HouseholdReferenceValue, etc.',
            default: str($indicator)->studly()->append('ReferenceValue')->toString(),
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/']],
            hint: "This will serve as the class name and has to be in camel case"
        );
        $level = select(
            label: "Please enter at which geographic level the source data is located at",
            options: AreaHierarchy::pluck('name', 'index')->reverse()->toArray(),
            hint: "You can also change this later in the class file",
        );
        $isAdditive = confirm(
            label: 'Are the reference values additive (across areas)?',
            default: true,
            hint: 'If yes, reference values for higher level areas will be summed up otherwise avg will be used'
        );
        $isAdditive = $isAdditive ? 'true' : 'false';

        $placeholders = compact('dataSource', 'indicator', 'level', 'isAdditive');
        if ($this->writeFile($classname, $placeholders)) {
            info('Reference value synthesizer class created successfully.');
        } else {
            throw new \Exception('There was a problem creating the class file');
        }

        return self::SUCCESS;
    }
}
