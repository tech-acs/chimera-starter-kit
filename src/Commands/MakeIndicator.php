<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Support\Collection;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\DataSource;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\search;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

class MakeIndicator extends GeneratorCommand
{
    protected $signature = 'chimera:make-indicator';
    protected $description = 'Create a new indicator component. Creates file from stub and adds entry in indicators table.';

    protected $type = 'default';
    protected $includeSampleCode = '';

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Livewire';
    }

    protected function getStub()
    {
        return resource_path("stubs/indicators/{$this->type}{$this->includeSampleCode}.stub");
    }

    protected function writeIndicatorFile(string $name, ?string $template = null)
    {
        $className = $this->qualifyClass($name);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        if (is_null($template)) {
            $content = $this->buildClass($className);
        } else {
            $fullTemplatePath = Storage::disk('indicator_templates')->path($template);
            $destinationPath = app_path() . "/IndicatorTemplates/$template";
            $this->makeDirectory($destinationPath);
            copy($fullTemplatePath, $destinationPath);
            $content = $this->buildClassWithTemplate($template, $className);
        }
        return $this->files->put($path, $content);
    }

    protected function buildClassWithTemplate(string $templateFile, string $className)
    {
        return str_replace(['DummyParentClass', '{{ parent_class }}', '{{parent_class}}'], str_replace('/', "\\", str_replace('.php', '', $templateFile)), $this->buildClass($className));
    }

    protected function loadIndicatorTemplates(): Collection
    {
        $directories = collect(Storage::disk('indicator_templates')->directories())
            ->filter(fn ($directory) => $directory !== 'docs');
        $files = [];
        foreach ($directories as $directory) {
            $files = array_merge($files, Storage::disk('indicator_templates')->files($directory));
        }
        $templates = collect($files)
            ->filter(fn ($file) => pathinfo($file, PATHINFO_EXTENSION) == 'php')
            ->map(function ($file) {
                return [
                    "Name" => str_replace('.php', '', pathinfo($file, \PATHINFO_FILENAME)),
                    "Category" => pathinfo($file, PATHINFO_DIRNAME),
                    "File" => $file
                ];
            });
        return $templates;
    }

    public function handle()
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        $name = text(
            label: "Indicator name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:indicators,name']],
            hint: "This will serve as the component name and has to be in camel case"
        );
        $dataSource = select(
            label: "Which data source will this indicator be using?",
            options: $dataSources->pluck('title', 'name')->toArray(),
            hint: "You will not be able to change this later"
        );

        $availableTemplates = $this->loadIndicatorTemplates();
        $chosenChartType = 'Default';
        $selectedTemplate = null;
        if ($availableTemplates->isNotEmpty()) {
            $useTemplate = confirm(
                label: 'Do you want to create the indicator from a template?',
                default: true,
                yes: 'Yes',
                no: 'No',
                hint: "There are {$availableTemplates->count()} templates to choose from"
            );
            if ($useTemplate) {
                $templateMenu = $availableTemplates
                    ->map(function ($template) {
                        $template['Label'] = "<fg=gray>{$template['Category']}:</> {$template['Name']}";
                        return $template;
                    });
                $selectedTemplate = search(
                    label: "Select the indicator template you want to use?",
                    options: fn (string $userInput) => strlen($userInput) > 0
                        ? $templateMenu->filter(function ($item, $key) use ($userInput) {
                            return str_starts_with(strtolower($item['Name']), strtolower($userInput));
                        })->pluck('Label', 'File')->toArray()
                        : $templateMenu->pluck('Label', 'File')->toArray(),
                    placeholder: 'Search...',
                    scroll: 10,
                    hint: "Type to search and use the arrow keys to select"
                );
                $chosenChartType = 'Template';
                $this->type = 'template';
                $this->includeSampleCode = false;
                $defaultTitle = str(basename($selectedTemplate))
                    ->snake()
                    ->before('.php')
                    ->replace('_', ' ')
                    ->title();
            }
        }

        if ($chosenChartType === 'Default') {
            $includeSampleCode = confirm(
                label: "Do you want the generated file to include functioning sample code?",
                default: true,
                yes: 'Yes',
                no: 'No',
                hint: "The sample code will help you to develop your own indicator logic"
            );
            $this->includeSampleCode = $includeSampleCode ? '-with-sample-code' : '';
        }

        $title = text(
            label: "Please enter a reader friendly title for the indicator",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            default: $defaultTitle ?? '',
            hint: "You can leave this empty for now",
        );
        $description = textarea(
            label: "Please enter a description for the indicator",
            placeholder: "E.g. This indicator represents the breakdown of the population by gender and age at different geographic levels.",
            default: $defaultDescription ?? '',
            hint: "You can leave this empty for now"
        );

        $indicator = Indicator::make([
            'name' => $name,
            'title' => $title,
            'description' => $description,
            'data_source' => $dataSource,
            'type' => $chosenChartType,
            'data' => [],
            'layout' => Storage::disk("plotly_defaults")->json("layout.json"),
        ]);
        DB::transaction(function () use ($indicator, $name, $selectedTemplate) {
            if ($this->writeIndicatorFile($name, $selectedTemplate)) {
                info('Indicator created successfully.');
            } else {
                throw new \Exception('There was a problem creating the class file');
            }
            $indicator->save();
        });

        return self::SUCCESS;
    }
}
