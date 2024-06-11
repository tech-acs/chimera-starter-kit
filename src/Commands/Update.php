<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Traits\PackageTasksTrait;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;

class Update extends Command
{
    public $signature = 'chimera:update {--composer=global}
        {--all : Runs all tasks (almost like new install)}
        {--chimera-config : Publishes chimera.php config file}
        {--migrations : Publishes migration files from chimera}
        {--packages : Installs php dependencies via composer}
        {--jetstream-customizations : Copies customized jetstream files from chimera}
        {--assets : Copies assets (css, js and stubs)}
        {--color-palettes : Copies color palettes from chimera}
        {--stubs : Publishes chimera stubs}
        {--npm : Installs node dependencies}
        {--copy-env : Copies .env.example from kit to .env and also generates key}';

    public $description = 'Update the Dashboard Starter Kit';

    use PackageTasksTrait;

    public function handle(): int
    {
        if (collect($this->options())->filter()->except('composer')->isEmpty()) {
            error('You have not specified any options');
            return self::FAILURE;
        }

        $runAll = $this->option('all') ?? false;
        $this->components->info("Updating Dashboard Starter Kit");

        if ($runAll || $this->option('chimera-config')) {
            $this->components->task('Publishing chimera config...', function () use ($runAll) {
                $this->callSilent('vendor:publish', ['--tag' => 'chimera-config', '--force' => true]);
            });
        }
        if ($runAll || $this->option('migrations')) {
            $this->components->task('Publishing chimera migrations...', function () use ($runAll) {
                $this->callSilent('vendor:publish', ['--tag' => 'chimera-migrations', '--force' => true]);
            });
        }
        if ($runAll || $this->option('packages')) {
            $this->installPhpDependencies();
        }
        if ($runAll || $this->option('jetstream-customizations')) {
            $this->copyCustomizedJetstreamFiles();
        }
        if ($runAll || $this->option('assets')) {
            $this->copyAssets();
        }
        if ($runAll || $this->option('color-palettes')) {
            $this->copyColorPalettes();
        }
        if ($runAll || $this->option('stubs')) {
            $this->components->task('Publishing stubs...', function () use ($runAll) {
                $this->callSilent('vendor:publish', ['--tag' => 'chimera-stubs', '----force' => true]);
            });
        }
        if ($runAll || $this->option('npm')) {
            $this->installJsDependencies();
        }
        if ($runAll || $this->option('copy-env')) {
            $this->installEnvFiles();
        }

        info('Update complete');
        return self::SUCCESS;
    }

}
