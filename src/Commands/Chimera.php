<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Traits\PackageTasksTrait;
use function Laravel\Prompts\info;

class Chimera extends Command
{
    public $signature = 'chimera:install {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    public $description = 'Install the Dashboard Starter Kit into your newly created Laravel application';

    use PackageTasksTrait;

    public function handle(): int
    {
        $this->installJetstream();
        $this->installPhpDependencies();
        $this->installHorizon();
        $this->publishVendorFiles();
        $this->copyCustomizedJetstreamFiles();
        $this->configureJetstreamFeatures();
        $this->copyAssets();
        $this->copyColorPalettes();
        $this->customizeExceptionRendering();
        $this->installEnvFiles();
        $this->installEmptyWebRoutesFile();
        $this->installJsDependencies();
        $this->cleanup();

        info("Installation complete");
        return self::SUCCESS;
    }
}
