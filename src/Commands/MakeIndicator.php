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

class MakeIndicator extends GeneratorCommand
{
    protected $signature = 'chimera:make-indicator';
    protected $description = 'Create a new indicator component. Creates file from stub and adds entry in indicators table.';

    protected $type = 'default';
    protected $includeSampleCode = '';
    protected $title = null;
    protected $template = null;

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Livewire';
    }

    protected function getStub()
    {
        return resource_path("stubs/indicators/{$this->type}{$this->includeSampleCode}.stub");
    }

    protected function writeIndicatorFile(string $name)
    {
        $className = $this->qualifyClass($name);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        if (empty($this->template)) {
            $content = $this->buildClass($className);
        } else {
            $template_path = Storage::disk('indicator_templates')->path($this->template['File']);
            $destination_path = app_path() . "/IndicatorTemplates/{$this->template['File']}";
            $this->makeDirectory($destination_path);
            copy($template_path, $destination_path);
            $content = $this->buildClassWithTemplate($className);
        }
        return $this->files->put($path, $content);
    }

    protected function buildClassWithTemplate($className)
    {
        return str_replace(['DummyParentClass', '{{ parent_class }}', '{{parent_class}}'], str_replace('/', "\\", str_replace('.php', '', $this->template['File'])), $this->buildClass($className));
    }

    protected function askForIndicatorTemplate()
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
    }

    protected function loadIndicatorTemplates(): Collection
    {
        $directories = collect(Storage::disk('indicator_templates')->directories())
            ->filter(fn ($directory) => $directory !== 'docs');
        $files = [];
        foreach ($directories as $directory) {
            $files = array_merge($files, Storage::disk('indicator_templates')->files($directory));
        }
        return collect($files)
            ->filter(fn ($file) => pathinfo($file, PATHINFO_EXTENSION) == 'php')
            ->map(function ($file) {
                return [
                    "Name" => str_replace('.php', '', pathinfo($file, \PATHINFO_FILENAME)),
                    "Category" => pathinfo($file, PATHINFO_DIRNAME),
                    "File" => $file
                ];
            });
    }

    public function handle()
    {
        if (DataSource::all()->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }
        $dataSources = DataSource::pluck('name')->toArray();
        $dataSourceMenu = array_combine(range(1, count($dataSources)), array_values($questionnaires));

        $name = text(
            label: "Indicator name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            hint: "This will serve as the component name and has to be in camel case",
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:indicators,name']]
        );

        $dataSource = select(
            label: "Which data source does this indicator belong to?",
            options: $dataSourceMenu
        );

        $this->askForIndicatorTemplate();

        if ($this->type == 'template') {
            $this->type = 'template';
            $chosenChartType = 'Template';
            $this->includeSampleCode = false;
            $this->title = str(basename($this->template['Name']))->snake()->replace('_', ' ')->title();
        } else {
            $chosenChartType = 'Default';
            $choice = select("Do you want the generated file to include functioning sample code?", [1 => 'yes', 2 => 'no'], 1);
            $this->includeSampleCode = $choice === 'yes' ? '-with-sample-code' : '';
        }
        $title = text(
            label: "Please enter a reader friendly title for the indicator",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
            default: $this->title,
        );
        $description = textarea(
            label: "Please enter a description for the indicator",
            placeholder: "",
            hint: "You can leave this empty for now"
        );
        DB::transaction(function () use ($name, $title, $description, $dataSource, $chosenChartType) {
            $result = $this->writeIndicatorFile($name);
            if ($result) {
                info('Indicator created successfully.');
            } else {
                throw new \Exception('There was a problem creating the indicator file');
            }
            Indicator::create([
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'questionnaire' => $dataSource,
                'type' => $chosenChartType,
            ]);
        });
        return self::SUCCESS;
    }
}
