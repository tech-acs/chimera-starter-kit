<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Uneca\Chimera\Traits\InstallUpdateTrait;

class Chimera extends Command
{
    public $signature = 'chimera:install {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    public $description = 'Install the Dashboard Starter Kit into your newly created Laravel application';

    use InstallUpdateTrait;

    public function handle(): int
    {
        $this->installJetstream();
        $this->comment('Installed Jetstream');

        $this->callSilent('vendor:publish', ['--tag' => 'chimera-config', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'chimera-migrations', '--force' => true]);
        $this->comment('Published chimera config and migrations');

        $this->requireComposerPackages($this->requiredComposerPackages);

        (new Process(['php', 'artisan', 'vendor:publish', '--provider=Spatie\Permission\PermissionServiceProvider', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });

        $this->copyJetstreamModifications();
        $this->comment('Copied Jetstream customizations');

        $this->publishResources();
        $this->comment('Published resources (js, css, public images, tailwind.config.js and vite.config.js)');

        $this->callSilent('vendor:publish', ['--tag' => 'chimera-stubs']);
        $this->comment('Published stubs');

        $this->callSilent('vendor:publish', ['--tag' => 'livewire:config']);
        $this->comment('Published livewire config');

        copy(__DIR__.'/../../deploy/web.php', base_path('routes/web.php'));
        $this->comment('Copied empty route file (web.php)');

        $this->editConfigFiles();
        $this->comment('Updated app, auth, and jetstream (enable: profile photo and terms + privacy | disable: account deletion) config files');

        // Exception handler (for token mismatch and invalid invitation exceptions) [??? try to not replace the file! Find a way!]
        copy(__DIR__.'/../../deploy/Handler.php', app_path('Exceptions/Handler.php'));

        $this->updateNodePackages(function ($packages) {
            return $this->requiredNodePackages + $packages;
        });
        $this->comment('Updated package.json with required npm packages');

        copy(__DIR__.'/../../deploy/.env.example', base_path('.env'));
        copy(__DIR__.'/../../deploy/.env.example', base_path('.env.example'));
        config(['app.key' => '']);
        $this->call('key:generate');
        $this->comment('Copied .env.example');

        $this->info('All done');
        $this->newLine();

        return self::SUCCESS;
    }
}
