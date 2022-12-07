<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

class Chimera extends Command
{
    public $signature = 'chimera:install {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    public $description = 'Install the Dashboard Starter Kit into your newly created Laravel application';

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

    protected function installRouteMiddlewareAfter($after, $name)
    {
        $httpKernel = file_get_contents(app_path('Http/Kernel.php'));
        $routeMiddleware = Str::before(Str::after($httpKernel, '$routeMiddleware = ['), '];');

        if (! Str::contains($routeMiddleware, $name)) {
            $modifiedRouteMiddleware = str_replace(
                $after.',',
                $after.','.PHP_EOL.'        '.$name.',',
                $routeMiddleware,
            );

            file_put_contents(app_path('Http/Kernel.php'), str_replace(
                $routeMiddleware,
                $modifiedRouteMiddleware,
                $httpKernel
            ));
        }
    }

    protected function installServiceProviderAfter($after, $name)
    {
        if (! Str::contains($appConfig = file_get_contents(config_path('app.php')), 'App\\Providers\\'.$name.'::class')) {
            file_put_contents(config_path('app.php'), str_replace(
                'App\\Providers\\'.$after.'::class,',
                'App\\Providers\\'.$after.'::class,'.PHP_EOL.'        App\\Providers\\'.$name.'::class,',
                $appConfig
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

        $this->requireComposerPackages([
            'ext-zip:*',
            'ext-pgsql:*',
            'spatie/laravel-permission:^5.7',
            'spatie/simple-excel:^2.4',
            'spatie/laravel-translatable:^6.1',
            'spatie/db-dumper:^3.3',
            'gasparesganga/php-shapefile:^3.4'
        ]);

        (new Process(['php', 'artisan', 'vendor:publish', '--provider=Spatie\Permission\PermissionServiceProvider', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });

        File::copyDirectory(__DIR__ . '/../../deploy/blade-component-classes', app_path('View/Components'));
        $this->comment('Copied blade component classes');

        copy(__DIR__.'/../../deploy/Kernel.php', app_path('Console/Kernel.php'));
        $this->comment('Copied Console/Kernel.php');

        File::copyDirectory(__DIR__ . '/../../deploy/controllers', app_path('Http/Controllers'));
        $this->comment('Copied controllers');

        File::copyDirectory(__DIR__ . '/../../deploy/livewire', app_path('Http/Livewire'));
        $this->comment('Copied livewire components');

        $this->copyFilesInDir(__DIR__ . '/../../deploy/actions/fortify', app_path('Actions/Fortify'));
        $this->comment('Copied actions');

        $this->copyFilesInDir(__DIR__ . '/../../deploy/middleware', app_path('Http/Middleware'));
        $this->comment('Copied middlewares');

        $this->copyFilesInDir(__DIR__ . '/../../deploy/models', app_path('Models'));
        $this->comment('Copied models');

        File::copyDirectory(__DIR__ . '/../../deploy/services', app_path('Services'));
        $this->comment('Copied Services');

        File::copyDirectory(__DIR__ . '/../../deploy/mail', app_path('Mail'));
        $this->comment('Copied Mail Classes');

        File::copyDirectory(__DIR__ . '/../../deploy/notifications', app_path('Notifications'));
        $this->comment('Copied Notification Classes');

        File::copyDirectory(__DIR__ . '/../../deploy/jobs', app_path('Jobs'));
        $this->comment('Copied Job Classes');

        File::copyDirectory(__DIR__ . '/../../deploy/rules', app_path('Rules'));
        $this->comment('Copied Rules');

        File::copyDirectory(__DIR__ . '/../../deploy/requests', app_path('Http/Requests'));
        $this->comment('Copied Requests');

        File::copyDirectory(__DIR__ . '/../../deploy/map-indicators', app_path('MapIndicators'));
        $this->comment('Copied MapIndicators');

        File::copyDirectory(__DIR__ . '/../../deploy/reports', app_path('Reports'));
        $this->comment('Copied Reports');

        File::copyDirectory(__DIR__ . '/../../deploy/views', resource_path('views'));
        $this->comment('Copied views');

        //File::makeDirectory(app_path('IndicatorTemplates'));
        //$this->comment('Created IndicatorTemplates directory');

        copy(__DIR__.'/../../deploy/jetstream-views/register.blade.php', resource_path('views/auth/register.blade.php'));
        $this->comment('Copied customized jetstream register view');

        $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/css', resource_path('css'), '*.css');
        //$this->copyFilesInDir(__DIR__ . '/../../deploy/resources/fonts', resource_path('fonts'), '*.*');
        $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/js', resource_path('js'), '*.js');
        File::copyDirectory(__DIR__ . '/../../deploy/resources/stubs', resource_path('stubs'));
        $this->comment('Copied resources');

        File::copyDirectory(__DIR__ . '/../../deploy/resources/lang', resource_path('lang'));
        $this->comment('Copied langs');

        $this->copyFilesInDir(__DIR__ . '/../../deploy/public/images', public_path('images'), '*.*');
        $this->comment('Copied public images');

        copy(__DIR__.'/../../deploy/npm/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__.'/../../deploy/npm/vite.config.js', base_path('vite.config.js'));
        $this->comment('Copied npm configs');

        $this->updateNodePackages(function ($packages) {
            return [
                "leaflet" => "^1.9.2",
                "plotly.js-basic-dist-min" => "^2.16.1",
                "plotly.js-locales" => "^2.16.1",
                "@alpinejs/focus" => "3.10.5",
                "@tailwindcss/line-clamp" => "^0.4.2"
            ] + $packages;
        });
        $this->comment('Updated package.json with required npm packages');

        copy(__DIR__.'/../../deploy/routes/web.php', base_path('routes/web.php'));
        $this->comment('Copied route file (web.php)');


        // Add things to Kernel.php
        $this->installMiddlewareAfter('SubstituteBindings::class', '\App\Http\Middleware\Language::class');
        $this->installMiddlewareAfter('SubstituteBindings::class', '\App\Http\Middleware\CheckAccountSuspension::class');
        $this->installRouteMiddlewareAfter('EnsureEmailIsVerified::class', "'log_page_views' => \App\Http\Middleware\LogPageView::class");
        $this->installRouteMiddlewareAfter('EnsureEmailIsVerified::class', "'enforce_2fa' => \App\Http\Middleware\RedirectIf2FAEnforced::class");

        // Service Providers...
        $this->copyFilesInDir(__DIR__.'/../../deploy/providers', app_path('Providers'));
        $this->installServiceProviderAfter('JetstreamServiceProvider', 'ChimeraServiceProvider');

        // Enable profile photo (jetstream)
        $this->replaceInFile('// Features::profilePhotos(),', 'Features::profilePhotos(),', config_path('jetstream.php'));

        // Disable account deletion (jetstream)
        $this->replaceInFile('Features::accountDeletion(),', '// Features::accountDeletion(),', config_path('jetstream.php'));

        // Make timezone setable from .env
        $this->replaceInFile("'timezone' => 'UTC'", "'timezone' => env('APP_TIMEZONE', 'UTC')", config_path('app.php'));

        // Exception handler (for token mismatch and invalid invitation exceptions)
        //$this->registerExceptionHandler($this->exceptionHandlingCallbacks());
        copy(__DIR__.'/../../deploy/Handler.php', app_path('Exceptions/Handler.php'));

        $this->info('All done');

        return self::SUCCESS;
    }

}
