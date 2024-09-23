<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Support\Collection;
use Uneca\Chimera\Models\ChartTemplate;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\DataSource;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;
use Uneca\Chimera\Traits\PlotlyDefaults;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\search;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

class MakeIndicator extends GeneratorCommand
{
    use PlotlyDefaults;

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
        $content = $this->buildClass($className);

        return $this->files->put($path, $content);
    }

    /*protected function buildClassWithTemplate(string $templateFile, string $className)
    {
        return str_replace(['DummyParentClass', '{{ parent_class }}', '{{parent_class}}'], str_replace('/', "\\", str_replace('.php', '', $templateFile)), $this->buildClass($className));
    }*/

    protected function loadIndicatorTemplates(): Collection
    {
        return ChartTemplate::orderBy('name')->get();
    }

    public function handle()
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        $dataSource = select(
            label: "Which data source will this indicator be using?",
            options: $dataSources->pluck('title', 'name')->toArray(),
            hint: "You will not be able to change this later"
        );

        $name = text(
            label: "Indicator name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            default: DataSource::whereName($dataSource)->first()->title . '/',
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:indicators,name']],
            hint: "This will serve as the component name and has to be in camel case"
        );

        $availableTemplates = $this->loadIndicatorTemplates();
        $chosenChartType = 'Default';
        $selectedTemplate = null;
        if ($availableTemplates->isNotEmpty()) {
            $useTemplate = confirm(
                label: 'Do you want to create the indicator from a template?',
                default: false,
                yes: 'Yes',
                no: 'No',
                hint: "There are {$availableTemplates->count()} templates to choose from"
            );
            if ($useTemplate) {
                $templateMenu = $availableTemplates
                    ->map(function ($template) {
                        $template->label = "<fg=gray>{$template->category}:</> {$template->name}";
                        return $template;
                    });
                $selectedTemplateId = search(
                    label: "Select the indicator template you want to use?",
                    options: fn (string $userInput) => strlen($userInput) > 0
                        ? $templateMenu->filter(function ($item, $key) use ($userInput) {
                            return str_starts_with(strtolower($item->name), strtolower($userInput));
                        })->pluck('label', 'id')->toArray()
                        : $templateMenu->pluck('label', 'id')->toArray(),
                    placeholder: __('Search...'),
                    scroll: 10,
                    hint: "Type to search and use the arrow keys to select"
                );
                $selectedTemplate = ChartTemplate::find($selectedTemplateId);
                $chosenChartType = 'Template';
                $this->type = 'template';
                $this->includeSampleCode = false;
                $defaultTitle = $selectedTemplate->name;
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
            'data' => $selectedTemplate?->data ?? [],
            'layout' => $selectedTemplate?->layout ?? self::DEFAULT_LAYOUT,
        ]);
        DB::transaction(function () use ($indicator, $name, $selectedTemplate) {
            if ($this->writeIndicatorFile($name)) {
                info('Indicator created successfully.');
            } else {
                throw new \Exception('There was a problem creating the class file');
            }
            $indicator->save();
        });

        return self::SUCCESS;
    }
}
