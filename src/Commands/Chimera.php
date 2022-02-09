<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class Chimera extends Command
{
    public $signature = 'chimera:install {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    public $description = 'Install the Census (CSPro) Dashboard Starter Kit components and resources';

    private function installJetstream()
    {
        $this->callSilent('jetstream:install', ['stack' => 'livewire']);
        $this->callSilent('vendor:publish', ['--tag' => 'jetstream-views']);
    }

    private function copyFilesInDir(string $srcDir, string $destDir, string $fileType = '*.php')
    {
        $fs = new Filesystem;
        $fs->ensureDirectoryExists($destDir);
        foreach (glob("$srcDir/$fileType") as $file) {
            $fs->copy($file, "$destDir/" . basename($file));
        }
    }

    /**
     * Update the "package.json" file.
     *
     * @param  callable  $callback
     * @param  bool  $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    /**
     * Delete the "node_modules" directory and remove the associated lock files.
     *
     * @return void
     */
    protected static function flushNodeModules()
    {
        tap(new Filesystem, function ($files) {
            $files->deleteDirectory(base_path('node_modules'));

            $files->delete(base_path('yarn.lock'));
            $files->delete(base_path('package-lock.json'));
        });
    }

    protected function installMiddlewareAfter($after, $name, $group = 'web')
    {
        $httpKernel = file_get_contents(app_path('Http/Kernel.php'));

        $middlewareGroups = Str::before(Str::after($httpKernel, '$middlewareGroups = ['), '];');
        $middlewareGroup = Str::before(Str::after($middlewareGroups, "'$group' => ["), '],');

        if (! Str::contains($middlewareGroup, $name)) {
            $modifiedMiddlewareGroup = str_replace(
                $after.',',
                $after.','.PHP_EOL.'            '.$name.',',
                $middlewareGroup,
            );

            file_put_contents(app_path('Http/Kernel.php'), str_replace(
                $middlewareGroups,
                str_replace($middlewareGroup, $modifiedMiddlewareGroup, $middlewareGroups),
                $httpKernel
            ));
        }
    }

    protected function requireComposerPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    public function handle(): int
    {
        $this->installJetstream();
        $this->comment('Installed jetstream');

        $this->callSilent('vendor:publish', ['--tag' => 'chimera-config', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'chimera-migrations', '--force' => true]);
        $this->comment('Published chimera config and migrations');

        $packages = [
            'spatie/laravel-permission:^4.0',
            'spatie/simple-excel:^1.13',
            'lasserafn/php-initial-avatar-generator:^4.2'
        ];
        $this->requireComposerPackages($packages);

        $this->callSilent('vendor:publish', ['--provider' => 'Spatie\Permission\PermissionServiceProvider', '--force' => true]);

        // blade-component-classes -> app/View/Components
        $this->copyFilesInDir(__DIR__ . '/../../deploy/blade-component-classes', app_path('View/Components'));
        $this->comment('Copied blade component classes');

        $this->copyFilesInDir(__DIR__ . '/../../deploy/commands', app_path('Console/Commands'));
        $this->comment('Copied commands');

        $this->copyFilesInDir(__DIR__ . '/../../deploy/controllers', app_path('Http/Controllers'));
        $this->comment('Copied controllers');

        $this->copyFilesInDir(__DIR__ . '/../../deploy/livewire', app_path('Http/Livewire'));
        $this->comment('Copied livewire components');

        // middleware -> app_path('Http/Middleware')
        $this->copyFilesInDir(__DIR__ . '/../../deploy/middleware', app_path('Http/Middleware'));
        $this->comment('Copied middlewares');

        // models -> app_path('Models')
        $this->copyFilesInDir(__DIR__ . '/../../deploy/models', app_path('Models'));
        $this->comment('Copied models');

        // app -> app_path('Services')
        File::copyDirectory(__DIR__ . '/../../deploy/services', app_path('Services'));
        $this->comment('Copied Services');

        // app -> app_path('Requests')
        File::copyDirectory(__DIR__ . '/../../deploy/requests', app_path('Http/Requests'));
        $this->comment('Copied Requests');

        // views -> resource_path('views')
        File::copyDirectory(__DIR__ . '/../../deploy/views', resource_path('views'));
        $this->comment('Copied views');

        // Assets...
        $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/css', resource_path('css'), '*.css');
        $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/fonts', resource_path('fonts'), '*.*');
        $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/js', resource_path('js'), '*.js');
        $this->comment('Copied resources');

        // langs
        File::copyDirectory(__DIR__ . '/../../deploy/resources/lang', resource_path('views'));
        $this->comment('Copied views');

        // Images
        $this->copyFilesInDir(__DIR__ . '/../../deploy/public/images', public_path('images'), '*.*');

        // Tailwind Configuration...
        copy(__DIR__.'/../../deploy/npm/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__.'/../../deploy/npm/webpack.mix.js', base_path('webpack.mix.js'));
        $this->comment('Copied npm configs');

        // NPM Packages...
        $this->updateNodePackages(function ($packages) {
            return [
                "leaflet" => "^1.7.1",
                "plotly.js-basic-dist" => "^2.8.0"
            ] + $packages;
        });
        $this->comment('Updated package.json with required packages');

        copy(__DIR__.'/../../deploy/routes/web.php', base_path('routes/web.php'));
        $this->comment('Copied route file (web.php)');

        // This one should be done by modifying the file, not copying it over
        // ... applies for AppServiceProvider and AuthServiceProvider
        copy(__DIR__.'/../../deploy/providers/AppServiceProvider.php', app_path('Providers/AppServiceProvider.php'));

        // Add 'log_page_views' => \App\Http\Middleware\LogPageView::class, and Language... to Kernel.php

        // Exception handler (for token mismatch and invalid invitation exceptions)
        

        $this->comment('All done');

        return self::SUCCESS;
    }
}
