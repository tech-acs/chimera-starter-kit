<?php

namespace Uneca\Chimera\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Uneca\Chimera\Traits\PackageTasksTrait;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class DeployHorizon extends Command
{
    protected $signature = 'chimera:deploy-horizon';
    protected $description = 'Deploy Laravel Horizon as the queue manager';

    use PackageTasksTrait;

    public function handle()
    {
        if (strtolower(PHP_OS) !== 'linux') {
            error('Laravel Horizon can only be used on Linux');
            return self::FAILURE;
        }
        if (! InstalledVersions::isInstalled('laravel/horizon')) {
            error('Laravel Horizon package is not installed. Please run "composer require laravel/horizon" and run this command again.');
            return self::FAILURE;
        }

        $this->components->info("Laravel Horizon: Queue monitoring and management");

        $this->components->task('Run horizon:install command', function () {
            return $this->callSilently('horizon:install');
        });
        $this->components->task('Publish custom HorizonServiceProvider', function () {
            return $this->callSilently('vendor:publish', ['--tag' => 'chimera-provider', '--force' => true]);
        });
        $this->components->task('Add developer-mode based horizon middleware to config/horizon.php', function () {
            return $this->replaceInFile("'middleware' => ['web'],", "'middleware' => ['web', 'horizon'],", config_path('horizon.php'));
        });

        info('Laravel Horizon successfully deployed. Make sure you install the service using provided template (horizon.service).');
        return self::SUCCESS;
    }
}
