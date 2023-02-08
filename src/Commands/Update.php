<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Traits\InstallUpdateTrait;

class Update extends Command
{
    public $signature = 'chimera:update {--composer=global} {--chimera-config} {--migrations} {--packages} {--jetstream-modifications} {--buildables} {--stubs} {--other-configs} {--npm} {--copy-env}';

    public $description = 'Update the Dashboard Starter Kit';

    use InstallUpdateTrait;

    public function handle(): int
    {
        if ($this->option('chimera-config')) {
            $this->callSilent('vendor:publish', ['--tag' => 'chimera-config', '--force' => true]);
            $this->comment('Published chimera config');
        }
        if ($this->option('migrations')) {
            $this->callSilent('vendor:publish', ['--tag' => 'chimera-migrations', '--force' => true]);
            $this->comment('Published migrations');
        }
        if ($this->option('packages')) {
            $this->requireComposerPackages($this->requiredComposerPackages);
            $this->comment('Updated composer.json');
        }
        if ($this->option('jetstream-modifications')) {
            $this->copyJetstreamModifications();
            $this->comment('Copied Jetstream customizations');
        }
        if ($this->option('buildables')) {
            $this->publishResources();
            $this->comment('Published resources (js, css, public images, tailwind.config.js and vite.config.js)');
        }
        if ($this->option('stubs')) {
            $this->callSilent('vendor:publish', ['--tag' => 'chimera-stubs']);
            $this->comment('Published stubs');
        }
        if ($this->option('other-configs')) {
            $this->editConfigFiles();
            $this->comment('Updated app, auth, and jetstream (enable: profile photo and terms + privacy | disable: account deletion) config files');
        }
        if ($this->option('npm')) {
            $this->updateNodePackages(function ($packages) {
                return $this->requiredNodePackages + $packages;
            });
            $this->comment('Updated package.json with required npm packages');
        }
        if ($this->option('copy-env')) {
            copy(__DIR__.'/../../deploy/.env.example', base_path('.env'));
            copy(__DIR__.'/../../deploy/.env.example', base_path('.env.example'));
            config(['app.key' => '']);
            $this->call('key:generate');
            $this->comment('Copied .env.example');
        }

        $this->newLine()->info('Update complete');
        $this->newLine();

        return self::SUCCESS;
    }

}
