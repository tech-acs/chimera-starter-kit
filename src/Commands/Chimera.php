<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class Chimera extends Command
{
    public $signature = 'chimera:install';

    public $description = 'Install the Census (CSPro) Dashboard Starter Kit components and resources';

    private function installJetstream()
    {
        $this->callSilent('jetstream:install', ['stack' => 'livewire']);
        $this->callSilent('vendor:publish', ['--tag' => 'jetstream-views']);
    }

    private function copyFilesInDir(string $srcDir, string $destDir, string $fileType = '*.php')
    {
        $fs = new Filesystem;
        foreach (glob("$srcDir/$fileType") as $file) {
            $fs->copy($file, "$destDir/" . basename($file));
        }
    }

    public function handle(): int
    {
        /*$this->installJetstream();
        $this->comment('Installed jetstream');

        $this->callSilent('vendor:publish', ['--tag' => 'chimera-config', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'chimera-migrations', '--force' => true]);
        $this->comment('Published chimera config and migrations');

        $this->copyFilesInDir(__DIR__ . '/../../code/controllers', app_path('Http/Controllers'));
        $this->comment('Copied controllers');

        $this->copyFilesInDir(__DIR__ . '/../../code/models', app_path('Models'));
        $this->comment('Copied models');*/

        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/livewire/resources/views/layouts', resource_path('views/layouts'));

        $this->comment('All done');

        return self::SUCCESS;
    }
}
