<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Uneca\Chimera\Traits\PackageTasksTrait;

class Chimera extends Command
{
    public $signature = 'chimera:install {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    public $description = 'Install the Dashboard Starter Kit into your newly created Laravel application';

    use PackageTasksTrait;

    public function handle(): int
    {
        $this->installJetstream();
        $this->publishVendorFiles();
        $this->installPhpDependencies();
        $this->copyCustomizedJetstreamFiles();
        $this->configureJetstreamFeatures();
        $this->copyAssets();
        $this->customizeExceptionRendering();
        $this->installEnvFiles();
        $this->installJsDependencies();

        $this->newLine();
        return self::SUCCESS;

        /*(new Process(['php', 'artisan', 'vendor:publish', '--provider=Spatie\Permission\PermissionServiceProvider', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });*/
    }
}
