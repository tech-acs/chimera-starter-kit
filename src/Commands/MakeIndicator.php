<?php

namespace Uneca\Chimera\Commands;

use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Traits\InteractiveCommand;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Uneca\Chimera\Models\Page;

class MakeIndicator extends GeneratorCommand
{
    use InteractiveCommand;
    protected $signature = 'chimera:make-indicator {name?} {--questionnaire=} {--type=} {--title=} {--description=} {--template=} {--package=}';
    protected $description = 'Create a new indicator component. If no indicator name is provided, the command will ask for it.';
    protected $template = null;
    protected $type = 'default';
    protected $includeSampleCode = '';
    protected $shouldPublish = false;
    protected $directory = null;
    protected $title = null;
    protected $templateName = null;
    protected $indicatorDescription = null;
    protected $package = null;
    protected $chosenChartType = null;
    protected $questionnaire = null;
    protected $dashboardPage = null;

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Livewire';
    }

    protected function getStub()
    {
        return resource_path("stubs/indicators/{$this->type}{$this->includeSampleCode}.stub");
    }

    protected function validateDashboardSetup()
    {

        //check if questionnaires exist
        if (Questionnaire::all()->isEmpty()) {

            $this->newLine();
            $this->error("You have not yet added questionnaires to your dashboard. Please do so first.");
            $this->error("Add questionnaires to your dashboard first.");
            return false;
        }

        //check if indicators is downloaded
        if (!Storage::disk('indicator_templates')->exists('docs')) {
            $this->newLine();
            $this->error("You have not yet downloaded indicator templates. Please do so first.");
            $this->error("Run `php artisan chimera:download-indicator-templates` to download indicator templates");
            return false;
        }

        return true;
    }


    protected function getQuestionnaire()
    {
        $questionnaires = Questionnaire::pluck('name')->toArray();
        $questionnaire = $this->option('questionnaire');
        if(!$questionnaire || !\array_search($questionnaire, $questionnaires)) {
            $questionnaireMenu = array_combine(range(1, count($questionnaires)), array_values($questionnaires));
            $questionnaire = $this->choice("Which questionnaire does this indicator belong to?", $questionnaireMenu);

        }
        $this->info("You have selected to use the questionnaire: <fg=white;bg=green>{$questionnaire}</>");
        $this->questionnaire = $questionnaire;
        return $this;
    }
    protected function getDashboardPage()
    {
        $dashboardPages = Page::pluck('slug')->toArray();
        $dashboardPage = \null;
        if(!\array_search($dashboardPage, $dashboardPages)) {
            $dashboardPageMenu = array_combine(range(1, count($dashboardPages)), array_values($dashboardPages));
            $dashboardPage = $this->choice("Which dashboard page does this indicator belong to?", $dashboardPageMenu);

        }
        $this->info("You have selected to use the page: <fg=white;bg=green>{$dashboardPage}</>");
        $this->dashboardPage = $dashboardPage;
        return $this;
    }

    protected function getIndicatorTemplate()
    {
        $this->type = $this->option('type') ?? 'default';
        $templates = $this->loadIndicatorTemplates();
        $templateNotFound = true;
        while ($templateNotFound) {
            $this->newLine();
            $this->info('You can use a template to create your indicator');
            $this->output->table(['Name', 'Category', 'Path'], $templates);

            $template = $this->anticipate('Select template you would like to use for your indicator(use arrow ⇅ to navigate)?', function ($input) use ($templates) {

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

        if ($this->type == 'template') {
            $this->chosenChartType = 'template';
            $this->includeSampleCode = false;
        } else {
            $this->chosenChartType = 'default';
            $choice = $this->choice("Do you want the generated file to include functioning sample code?", [1 => 'yes', 2 => 'no'], 1);
            $this->includeSampleCode = $choice === 'yes' ? '-with-sample-code' : '';
        }
        return $this;
    }

    protected function getIndicatorName()
    {
        $this->name = $this->argument('name');
        if(!$this->name) {
            $suggestName = '';
            if($this->type == 'template'){
                $this->name = str(basename($this->template['Name']));
                $suggestName = $this->name ?? 'HouseholdsEnumeratedByDay';
                $this->name = $this->anticipateValid(
                    "Please provide a name for the indicator\n\n (This will serve as the component name and has to be in camel case. Eg. '{$suggestName}'\n You can also include directory to help with organization of indicator files. Eg. {$this->questionnaire}/{$suggestName}')",
                    'name',
                    ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:indicators,name'],[$suggestName]
                );
            }
            else{

                $this->name = $this->askValid(
                    "Please provide a name for the indicator\n\n (This will serve as the component name and has to be in camel case. Eg. 'HouseholdsEnumeratedByDay'\n You can also include directory to help with organization of indicator files. Eg. {$this->questionnaire}/HouseholdsEnumeratedByDay')",
                    'name', ['required', 'string', 'regex:/^[A-Za-z][A-Za-z\/]*$/', 'unique:indicators,name']
                );
            }

            $this->name = $this->name ?? $suggestName;
        }
        $this->info("You have selected to use the name: <fg=white;bg=green>{$this->name}</>");
        return $this;
    }

    protected function getIndicatorTitle()
    {
        $this->title = $this->option('title');
        if (!$this->title) {
            if($this->type == 'template')
                $suggestedTitle = str(basename($this->template['Name']))->snake()->replace('_', ' ')->title();
            else
                $suggestedTitle = str($this->name)->snake()->replace('/',':')->replace('_', ' ')->title();

            $this->title = $this->askValid(
                "Please enter a reader friendly title for the indicator (press enter to set {$suggestedTitle}) for now) ",
                'title',
                ['nullable',]
            );
            $this->title = $this->title ?? str($this->name)->snake()->replace('_', ' ')->title();
        }
        $this->info("You have selected to use the title: <fg=white;bg=green>{$this->title}</>");
        return $this;
    }

    protected function getIndicatorDescription()
    {
        $this->indicatorDescription = $this->option('description');
        if(!$this->indicatorDescription) {
                $this->indicatorDescription = '';
                $this->indicatorDescription = $this->askValid(
                    "Please enter a description for the indicator (press enter to leave empty for now)",
                    'description',
                    ['nullable',]
                );
        }
        $this->info("You have selected to use the description: <fg=white;bg=green>{$this->indicatorDescription}</>");
        return $this;
    }

    protected function handleMakeIndicatorFromPackages($packageName)
    {
        $this->info("You have selected to use indicator templates from the package: <fg=white;bg=green>$packageName</>");
        $this->info("The following indicator templates are available:");
        $templateList = $this->loadIndicatorTemplatesFromPackage($packageName);
        $this->table(['Name', 'Category', 'Path'], \array_map(function ($template) {
            return [$template['Name'], $template['Category'], $template['Path']];
        }, $templateList));


        $this->directory = $this->askValid(
            "Please enter a directory for the indicators to be placed in (press enter to leave empty)",
            'directory',
            ['nullable',]
        );

       $this->shouldPublish = $this->choice(
            "Do you want to publish the indicator templates from the package $packageName to your dashboard? (yes/no)",[1=>'yes',2=>'no'],2)=='yes';

        if($this->shouldPublish){
            $this->getDashboardPage();
        }

        $this->type = 'template';
        foreach ($templateList as $template) {
            $name = $this->directory ? $this->directory . '/' . $template['Name'] : $template['Name'];
            $this->createIndicator($name,$template['Title'],$template['Description'],$this->questionnaire,'Template',$template,$this->shouldPublish,$this->dashboardPage);
        }


        $this->newLine();
        $this->info("Indicator templates from the package $packageName have been added to your dashboard.");
        $this->newLine();

        return true;
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
        return $templates;
    }

    protected function loadIndicatorTemplatesFromPackage($packageName)
    {

        $package = Storage::disk('indicator_templates')->get("__packages/{$packageName}.json");
        if (!$package) {
            $this->error("Package {$packageName} not found");
            return false;
        }
        $this->info($package);

        $content = json_decode($package, true);

        $packageTemplates = $content['indicators'];
        return \array_map(
            function ($template) {
                $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $template['slug_id'])));
                return
                $templates[] = [
                    "Name" => $className,
                    "Category" => $template['categories'],
                    "Path" => $template['categories'] . '/' . $className . '.php',
                    "Title" => $template['title'],
                    "Description" => $template['description'],
                ];
            },
            $packageTemplates
        );

    }
    protected function buildClassWithTemplate($className, $template,$sampleContent='')
    {
        $file_content = str_replace(['DummyParentClass', '{{ parent_class }}', '{{parent_class}}'], str_replace('/', "\\", str_replace('.php', '', $template['Path'])), $this->buildClass($className));
        $file_content = str_replace(['{{ content }}', '{{content}}'], $sampleContent, $file_content);
        return $file_content;
    }

    protected function writeIndicatorToFile(string $name, $template)
    {
        $className = $this->qualifyClass($name);

        $path = $this->getPath($className);
        $this->makeDirectory($path);

        if (empty($template)) {
            $content = $this->buildClass($className);
        } else {
            $template_path = Storage::disk('indicator_templates')->path($template['Path']);
            $destination_path = \app_path() . "/IndicatorTemplates/{$template['Path']}";
            $content = '';
            foreach (\token_get_all(\file_get_contents($template_path)) as $token) {
                if (is_array($token) && in_array($token[0],[\T_COMMENT,\T_DOC_COMMENT])) {
                    $content .= $token[1].PHP_EOL;
                }
            }
            $this->makeDirectory($destination_path);
            \copy($template_path, $destination_path);
            $content = $this->buildClassWithTemplate($className, $template,$content);
        }
        return $this->files->put($path, $content);
    }

    public function buildIndicator(){
        return $this->createIndicator($this->name, $this->title, $this->description, $this->questionnaire, $this->chosenChartType, $this->template);
    }

    protected function createIndicator($name, $title ,
    $description , $questionnaire ,
     $chosenChartType , $template=null,$publish=false,$page=null)
    {

        DB::transaction(function () use ($name, $title, $description, $questionnaire, $chosenChartType, $template,$publish,$page) {
            $result = $this->writeIndicatorToFile($name, $template);

            if ($result && $chosenChartType == 'Template') {
                $this->info("Indicator {$name} with template {$template['Name']} from {$template['Path']} created successfully.");
            }
            else if($result){
                $this->info("Indicator {$name} created successfully.");
            } else {
                throw new \Exception('There was a problem creating the indicator file');
            }

            $indicator = Indicator::create([
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'questionnaire' => $questionnaire,
                'type' => $chosenChartType,
                'published' => $publish,
            ]);

            if($page){
                $dashboardPage = Page::where('slug',$page)->first()->id;
                $indicator->pages()->sync([$dashboardPage]);
            }
        });
    }

    public function handle()
    {
        $this->info("Welcome to the indicator generator.");
        $this->package = $this->option('package');

        if ($this->validateDashboardSetup()) {
            $this->getQuestionnaire();

            if ($this->package) {
                return $this->handleMakeIndicatorFromPackages($this->package);
            } else {
                $this->getIndicatorTemplate()
                     ->getIndicatorName()
                     ->getIndicatorTitle()
                     ->getIndicatorDescription()
                     ->buildIndicator();
                $this->info("Indicator {$this->title} has been added to your dashboard.");
                return true;
            }
        }
        return false;
    }
}
