<?php

namespace Uneca\Chimera\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use Uneca\Chimera\ChimeraServiceProvider;

class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Uneca\\CensusDashboardStarterKit\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ChimeraServiceProvider::class,
            \Opcodes\LogViewer\LogViewerServiceProvider::class,
            \Laravel\Mcp\Server\McpServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('session.driver', 'array');
    }
}
