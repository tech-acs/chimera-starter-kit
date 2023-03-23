<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Traits\InstallUpdateTrait;
use Illuminate\Support\Facades\Storage;

class UpdateIndicators extends Command
{
    use InstallUpdateTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //--class is optional
    public $signature = 'chimera:update-indicators {--all} {--force : Force update without confirmation} {--class= : Update a specific indicator}';


    public $description = 'Update Indicators from template';

    public function handle(): int
    {
        if (!$this->confirm('This will update all indicators. Are you sure?', \false)) {
            $this->newLine()->info('Update aborted');
            return 0;
        }
        $this->newLine()->info('Updating...');
        if ($this->option('all') or $this->option('class')) {
            $this->updateIndicator($this->option('class'));
        } else {
            $this->updateAllIndicators();
        }

        $this->newLine()->info('Update complete');
        return 0;
    }

    public function updateIndicator($class)
    {
        $template_path = Storage::disk('indicator_templates')->get($class . '.php');
        $destination_path = \app_path() . "/IndicatorTemplates/{$class}.php'";
        \copy($template_path, $destination_path);
    }

    public function getTemplateList()
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
        return $files;
    }

    public function updateAllIndicators()
    {
        $indicator_path = \app_path() . "/IndicatorTemplates";
        //find all files in the indicator path that are php files including subdirectories
        $files = glob($indicator_path . '/**/*.php');
        //for each file if it exists in the indicator_templates disk then copy file in indicator_templates to indicator_path
        foreach ($files as $file) {
            $file_name = str_replace($indicator_path . '/', '', $file);
            if (Storage::disk('indicator_templates')->exists($file_name)) {
                $template_path = Storage::disk('indicator_templates')->path($file_name);
                $destination_path = \app_path() . "/IndicatorTemplates/{$file_name}";
                \copy($template_path, $destination_path);
                $this->newLine()->info('Updated ' . $file_name);
            }
        }
    }
}
