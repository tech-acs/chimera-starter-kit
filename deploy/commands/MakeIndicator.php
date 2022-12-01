<?php

namespace App\Console\Commands;

use App\Models\Indicator;
use App\Models\Questionnaire;
use App\Services\Traits\InteractiveCommand;
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
        if (is_null($this->template)) {
            $content = $this->buildClass($className);
        } else {

            $template_path = Storage::disk('indicator_templates')->path($this->template).'.php';
            $destination_path = \app_path()."/IndicatorTemplates/{$this->template}.php";
            $this->makeDirectory($destination_path);
            \copy($template_path,$destination_path);
            $content = $this->buildClassWithTemplate($className);
        }
        return $this->files->put($path, $content);
    }
    protected function buildClassWithTemplate($className){
        
        $content = str_replace(['DummyParentClass', '{{ parent_class }}', '{{parent_class}}'], str_replace('/',"\\",$this->template), $this->buildClass($className));
        return $content;
    }

    protected function askForIndicatorTemplate()
    {
        $templates = $this->loadIndicatorTemplates();
        $templateNotFound = true;
        while ($templateNotFound) {
            $template = $this->anticipate('Select template you would like to use for your indicator(use arrow ⇅ to navigate)?', $templates, null);
            if (in_array($template, $templates)) {
                $templateNotFound = false;
                $this->type = 'template';
                $this->template = str_replace('.php', '', $template);
            } elseif (is_null($template)) {
                $templateNotFound = false;
            } else {
                $this->error('Template not found');
            }
        }
    }

    protected function loadIndicatorTemplates()
    {

        $files = Storage::disk('indicator_templates')->allFiles();
        $templates = array_map(function ($file) {
            //check if file is a php file
            if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                return str_replace('.php', '', $file);
            }
        }, $files);
        return $templates;
    }

    public function handle(): bool|null
    {
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
