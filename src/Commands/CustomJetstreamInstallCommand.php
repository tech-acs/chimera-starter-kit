<?php

namespace Uneca\Chimera\Commands;



use Laravel\Jetstream\Console\InstallCommand;

class CustomJetstreamInstallCommand extends InstallCommand
{
    protected $signature = 'jetstream:custom-install {stack : The development stack that should be installed (inertia,livewire)}
                                              {--dark : Indicate that dark mode support should be installed}
                                              {--teams : Indicates if team support should be installed}
                                              {--api : Indicates if API support should be installed}
                                              {--verification : Indicates if email verification support should be installed}
                                              {--pest : Indicates if Pest should be installed}
                                              {--ssr : Indicates if Inertia SSR support should be installed}
                                              {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    protected $description = "A customized Jetstream install command (removes 'npm install' and 'artisan migrate')";

    public function handle()
    {
        return parent::handle();
    }

    protected function runCommands($commands)
    {
        // Do nothing
    }

    protected function runDatabaseMigrations()
    {
        // Do nothing
    }

    protected function livewireRouteDefinition()
    {
        return '';
    }
}
