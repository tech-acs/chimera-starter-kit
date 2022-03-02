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

    protected function registerExceptionHandler($contentToAppend)
    {
        $handler = file_get_contents(app_path("Exceptions/Handler.php"));
        $bootMethodContents = Str::before(Str::after($handler, 'function register()'), '}' . PHP_EOL);

        file_put_contents(app_path("Exceptions/Handler.php"), str_replace(
            $bootMethodContents,
            $bootMethodContents . PHP_EOL . '    ' . $contentToAppend . PHP_EOL,
            $handler
        ));
    }

    protected function exceptionHandlingCallbacks()
    {
        return <<<'EOF'
	$this->renderable(function (\Illuminate\Routing\Exceptions\InvalidSignatureException $e) {
			return response()->view('error.link-invalid', [], 403);
		});

		$this->renderable(function (Throwable $e) {
			if ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
				app('redirect')->setIntendedUrl(url()->previous());
				return redirect()->route('login')
					->withInput(request()->except('_token'))
					->withErrors('Security token has expired. Please sign-in again.');
			}
		});
EOF;
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
        //$this->requireComposerPackages('laravel/jetstream:^2.6');
        $this->installJetstream();
        $this->comment('Installed jetstream');

        $this->callSilent('vendor:publish', ['--tag' => 'chimera-config', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'chimera-migrations', '--force' => true]);
        $this->comment('Published chimera config and migrations');

        $this->requireComposerPackages([
            'spatie/laravel-permission:^5.5', 
            'spatie/simple-excel:^1.15',
            'spatie/laravel-translatable:^5.2'
        ]);

        (new Process(['php', 'artisan', 'vendor:publish', '--provider=Spatie\Permission\PermissionServiceProvider', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });

        // blade-component-classes -> app/View/Components
        $this->copyFilesInDir(__DIR__ . '/../../deploy/blade-component-classes', app_path('View/Components'));
        $this->comment('Copied blade component classes');

        $this->copyFilesInDir(__DIR__ . '/../../deploy/commands', app_path('Console/Commands'));
        $this->comment('Copied commands');

        File::copyDirectory(__DIR__ . '/../../deploy/controllers', app_path('Http/Controllers'));
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
        $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/stubs', resource_path('stubs'), '*.*');
        $this->comment('Copied resources');

        // langs
        File::copyDirectory(__DIR__ . '/../../deploy/resources/lang', resource_path('lang'));
        $this->comment('Copied langs');

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
        

        // Add 'log_page_views' => \App\Http\Middleware\LogPageView::class, and Language... to Kernel.php
        $this->installMiddlewareAfter('SubstituteBindings::class', '\App\Http\Middleware\Language::class');
        $this->installRouteMiddlewareAfter('EnsureEmailIsVerified::class', "'log_page_views' => \App\Http\Middleware\LogPageView::class");

        // Service Providers...
        copy(__DIR__.'/../../deploy/providers/ChimeraServiceProvider.php', app_path('Providers/ChimeraServiceProvider.php'));
        $this->installServiceProviderAfter('JetstreamServiceProvider', 'ChimeraServiceProvider'); 

        // Enable profile photo (jetstream)
        $this->replaceInFile('// Features::profilePhotos(),', 'Features::profilePhotos(),', config_path('jetstream.php'));

        // Exception handler (for token mismatch and invalid invitation exceptions)
        $this->registerExceptionHandler($this->exceptionHandlingCallbacks());
        

        $this->comment('All done');

        return self::SUCCESS;
    }

}
