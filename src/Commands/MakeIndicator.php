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
use function Laravel\Prompts\table;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
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

    /*protected function askForIndicatorTemplate()
    {
        $templates = $this->loadIndicatorTemplates();
        $templateNotFound = true;
        while ($templateNotFound) {
            info('The following indicator templates are available for use.');
            table(
                ['Name', 'Category'],
                $templates->map(function ($template) {
                    unset($template['File']);
                    return $template;
                })
            );
            $selectedTemplate = search(
                label: 'Select the template you would like to use for your indicator',
                placeholder: "Select 'Blank indicator' if you do not want to use a template",
                options: fn (string $userInput) => strlen($userInput) > 0
                    ? $templates->filter(function ($item, $key) use ($userInput) {
                        return str_starts_with(strtolower($item['Name']), strtolower($userInput));
                    })->pluck('Name')->toArray()
                    : $templates->pluck('Name')->toArray(),
                hint: 'Type to search and use the arrow keys to select'
            );
            dump($selectedTemplate);
            $collection = collect($templates);
            if ($selected = $collection->firstWhere('Name', $selectedTemplate)) {
                $templateNotFound = false;
                $this->type = 'template';
                $this->template = $selected;
            } elseif (empty($selectedTemplate)) {
                $templateNotFound = false;
            } else {
                error('Template not found');
            }
        }
    }*/

    protected function loadIndicatorTemplates(): Collection
    {
        $templates = collect([]);
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
            hint: "This will serve as the component name and has to be in camel case",
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:indicators,name']]
        );

        $dataSource = select(
            label: "Which data source will this indicator be using?",
            options: $dataSources->pluck('name', 'name')->toArray(),
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
                    placeholder: 'Search...',
                    options: fn (string $userInput) => strlen($userInput) > 0
                        ? $templateMenu->filter(function ($item, $key) use ($userInput) {
                            return str_starts_with(strtolower($item['Name']), strtolower($userInput));
                        })->pluck('Label', 'File')->toArray()
                        : $templateMenu->pluck('Label', 'File')->toArray(),
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
            $includeSampleCode = select(
                label: "Do you want the generated file to include functioning sample code?",
                options: ['Yes', 'No'],
                default: 'Yes',
                hint: "This will help you to develop your own indicator logic"
            );
            $this->includeSampleCode = $includeSampleCode === 'Yes' ? '-with-sample-code' : '';
        }

        $title = text(
            label: "Please enter a reader friendly title for the indicator",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
            default: $defaultTitle ?? '',
        );
        $description = textarea(
            label: "Please enter a description for the indicator",
            placeholder: "E.g. This indicator represents the breakdown of the population by gender and age at different geographic levels.",
            hint: "You can leave this empty for now",
            default: $defaultDescription ?? ''
        );
        $indicator = Indicator::make([
            'name' => $name,
            'title' => $title,
            'description' => $description,
            'data_source' => $dataSource,
            'type' => $chosenChartType,
        ]);
        DB::transaction(function () use ($indicator, $name, $selectedTemplate) {
            $result = $this->writeIndicatorFile($name, $selectedTemplate);
            if ($result) {
                info('Indicator created successfully.');
            } else {
                throw new \Exception('There was a problem creating the indicator file');
            }
            $indicator->save();
        });
        return self::SUCCESS;
    }
}
