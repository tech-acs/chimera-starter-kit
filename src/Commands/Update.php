<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Traits\PackageTasksTrait;
use function Laravel\Prompts\info;

class Update extends Command
{
    public $signature = 'chimera:update {--composer=global} {--chimera-config} {--migrations} {--packages} {--jetstream-customizations} {--assets} {--color-palettes} {--stubs} {--npm} {--copy-env}';

    public $description = 'Update the Dashboard Starter Kit';

    use PackageTasksTrait;

    public function handle(): int
    {
        if ($this->option('chimera-config')) {
            $this->callSilent('vendor:publish', ['--tag' => 'chimera-config', '--force' => true]);
            info('Published chimera config');
        }
        if ($this->option('migrations')) {
            $this->callSilent('vendor:publish', ['--tag' => 'chimera-migrations', '--force' => true]);
            info('Published chimera migrations');
        }
        if ($this->option('packages')) {
            $this->installPhpDependencies();
        }
        if ($this->option('jetstream-customizations')) {
            $this->copyCustomizedJetstreamFiles();
        }
        if ($this->option('assets')) {
            $this->copyAssets();
        }
        if ($this->option('color-palettes')) {
            $this->copyColorPalettes();
        }
        if ($this->option('stubs')) {
            $this->callSilent('vendor:publish', ['--tag' => 'chimera-stubs']);
            info('Published stubs');
        }
        if ($this->option('npm')) {
            $this->installJsDependencies();
        }
        if ($this->option('copy-env')) {
            $this->installEnvFiles();
        }
        info('Update complete');

        return self::SUCCESS;
    }

}
