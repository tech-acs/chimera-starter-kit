<?php

namespace Uneca\Chimera\Commands;

use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Traits\InteractiveCommand;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MakeIndicator extends GeneratorCommand
{
    use InteractiveCommand;
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
            $template_path = Storage::disk('indicator_templates')->path($this->template['Path']);
            $destination_path = \app_path() . "/IndicatorTemplates/{$this->template['Path']}";
            $this->makeDirectory($destination_path);
            \copy($template_path, $destination_path);
            $content = $this->buildClassWithTemplate($className);
        }
        return $this->files->put($path, $content);
    }
    protected function buildClassWithTemplate($className)
    {
        $content = str_replace(['DummyParentClass', '{{ parent_class }}', '{{parent_class}}'], str_replace('/', "\\", str_replace('.php', '', $this->template['Path'])), $this->buildClass($className));
        return $content;
    }

    protected function askForIndicatorTemplate()
    {
        $templates = $this->loadIndicatorTemplates();
        $templateNotFound = true;
        while ($templateNotFound) {
            $this->newLine();
            $this->info('You can use a template to create your indicator');
            $this->output->table(['Name', 'Category', 'Path'], $templates);

            $template = $this->anticipate('Select template you would like to use for your indicator(use arrow ⇅ to navigate)?', function ($input) use ($templates) {
                // $tableSection->output->table(['Name', 'Category', 'Path'], array_filter($templates, function ($template) use ($input) {
                //     return str_contains($template['Name'], $input);
                // }));
                return array_map(function ($row) {
                    return $row['Name'];
                }, array_filter($templates, function ($template) use ($input) {
                    return str_contains($template['Name'], $input);
                }));
            }, null);
            $collection = \collect($templates);
            if ($selected = $collection->firstWhere('Name', $template)) {
                $templateNotFound = false;
                $this->type = 'template';
                $this->template = $selected;
            } elseif (empty($template)) {
                $templateNotFound = false;
            } else {
                $this->error('Template not found');
            }
        }
    }

    protected function loadIndicatorTemplates()
    {
        $directories = Storage::disk('indicator_templates')->directories();
        // filter docs directory
        $directories = array_filter($directories, function ($directory) {
            return $directory !== 'docs';
        });
        $files = [];
        foreach ($directories as $directory) {
            $files = array_merge($files, Storage::disk('indicator_templates')->files($directory));
        }

        $templates = array_map(function ($file) {
            return ["Name" => str_replace('.php', '', \pathinfo($file, \PATHINFO_FILENAME)), "Category" => \pathinfo($file, PATHINFO_DIRNAME), "Path" => $file];
        }, array_filter($files, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) == 'php';
        }));
        // dd($templates);
        return $templates;
    }

    public function handle(): bool|null
    {
        // $section = $this->output->section();
        // $table = new Table($section);

        // $table->addRow(['Love']);
        // $table->render();

        // $table->appendRow(['Symfony']);

        if (Questionnaire::all()->isEmpty()) {
            $this->newLine();
            $this->error("You have not yet added questionnaires to your dashboard. Please do so first.");
            $this->newLine();
            return false;
        }

        $name = $this->askValid(
            "Please provide a name for the indicator\n\n (This will serve as the component name and has to be in camel case. Eg. HouseholdsEnumeratedByDay\n You can also include directory to help with organization of indicator files. Eg. Household/BirthRate)",
            'name',
            ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:indicators,name']
        );

        $questionnaires = Questionnaire::pluck('name')->toArray();
        $questionnaireMenu = array_combine(range(1, count($questionnaires)), array_values($questionnaires));
        $questionnaire = $this->choice("Which questionnaire does this indicator belong to?", $questionnaireMenu);
        $this->askForIndicatorTemplate();

        if ($this->type == 'template') {
            $this->type = 'template';
            $chosenChartType = 'Template';
            $this->includeSampleCode = false;
            $this->title = str(basename($this->template['Name']))->snake()->replace('_', ' ')->title();
        } else {
            $chosenChartType = 'Default';
            $choice = $this->choice("Do you want the generated file to include functioning sample code?", [1 => 'yes', 2 => 'no'], 1);
            $this->includeSampleCode = $choice === 'yes' ? '-with-sample-code' : '';
        }


        $title = $this->askValid(
            "Please enter a reader friendly title for the indicator (press enter to set " . ($this->title ?? 'empty') . " for now) ",
            'title',
            ['nullable',]
        );

        $title = $title ?? $this->title;

        $description = $this->askValid(
            "Please enter a description for the indicator (press enter to leave empty for now)",
            'description',
            ['nullable',]
        );
        DB::transaction(function () use ($name, $title, $description, $questionnaire, $chosenChartType) {
            $result = $this->writeIndicatorFile($name);
            if ($result) {
                $this->info('Indicator created successfully.');
            } else {
                throw new \Exception('There was a problem creating the indicator file');
            }
            Indicator::create([
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'questionnaire' => $questionnaire,
                'type' => $chosenChartType,
            ]);
        });
        return true;
    }
}
