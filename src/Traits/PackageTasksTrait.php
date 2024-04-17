<?php

namespace Uneca\Chimera\Traits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

trait PackageTasksTrait
{
    public array $requiredNodePackages = [
        "leaflet" => "^1.9",
        "plotly.js-basic-dist-min" => "^2.30",
        "plotly.js-locales" => "^2.30",
        "alpinejs" => "3.13",
        //"@alpinejs/focus" => "3.13",
        //"@tailwindcss/line-clamp" => "^0.4.2",
        "@tailwindcss/aspect-ratio" => "^0.4.2",
        "lodash" => "^4.17.21",
    ];

    public array $phpDependencies = [
        'ext-zip:*',
        'ext-pgsql:*',
        'gasparesganga/php-shapefile:^3.4',
        'spatie/laravel-permission:^6.4',
        'spatie/simple-excel:^3.5',
        'spatie/laravel-translatable:^6.1',
        'spatie/db-dumper:^3.5',
    ];

    public array $vendorPublish = [
        'Chimera config' => ['--tag=chimera-config', '--force'],
        'Chimera migrations' => ['--tag=chimera-migrations', '--force'],
        'Chimera stubs' => ['--tag=chimera-stubs'],
        'Livewire config' => ['--tag=livewire:config'],
        'Spatie permissions' => ['--provider=Spatie\Permission\PermissionServiceProvider', '--force']
    ];

    public array $customizedJetstreamViews = [
        'register.blade.php' => 'views/auth/register.blade.php',
        'app.blade.php' => 'views/layouts/app.blade.php',
        'guest.blade.php' => 'views/layouts/guest.blade.php',
        'navigation-menu.blade.php' => 'views/navigation-menu.blade.php',
        'show.blade.php' => 'views/profile/show.blade.php',
        'area-restriction.blade.php' => 'views/profile/area-restriction.blade.php'
    ];

    protected function installJetstream(): void
    {
        $this->components->info("This kit is built on top of Laravel Jetstream");
        $this->components->task('Installing Laravel Jetstream (takes time)', function () {
            return $this->callSilently('jetstream:custom-install', ['stack' => 'livewire', '--quiet' => true]);
        });
    }

    protected function installPhpDependencies(): void
    {
        $this->components->info("Php dependencies");
        $this->components->bulletList($this->phpDependencies);
        $this->components->task('Installing composer packages (takes time)', function () {
            return $this->requireComposerPackages($this->phpDependencies);
        });
    }

    protected function publishVendorFiles(): void
    {
        $this->components->info("Publishing vendor files");
        foreach ($this->vendorPublish as $vendorItem => $options) {
            $this->components->task($vendorItem, function () use ($options) {
                return (new Process(array_merge(['php', 'artisan', 'vendor:publish'], $options), base_path()))
                    ->setTimeout(null)
                    ->run(function ($type, $output) {
                        //$this->output->write($output);
                    });
            });
        }
    }

    protected function copyCustomizedJetstreamFiles(): void
    {
        $this->components->info("Copying customized Jetstream files");
        $this->components->task('Jetstream actions', function () {
            return $this->copyFilesInDir(__DIR__ . '/../../deploy/jetstream-modifications/actions', app_path('Actions/Fortify'));
        });
        foreach ($this->customizedJetstreamViews as $source => $destination) {
            $this->components->task($source, function () use ($source, $destination) {
                return copy(__DIR__ . "/../../deploy/jetstream-modifications/views/$source", resource_path($destination));
            });
        }
    }

    protected function configureJetstreamFeatures(): void
    {
        $this->components->info("Configuring jetstream features");
        $options = [
            'Enable terms and privacy policy' => [
                'search' => '// Features::termsAndPrivacyPolicy(),',
                'replace' => 'Features::termsAndPrivacyPolicy(),'
            ],
            'Enable profile photos' => [
                'search' => '// Features::profilePhotos(),',
                'replace' => 'Features::profilePhotos(),'
            ],
            'Disable account deletion' => [
                'search' => 'Features::accountDeletion(),',
                'replace' => '// Features::accountDeletion(),'
            ],
        ];
        foreach ($options as $feature => $option) {
            $this->components->task($feature, function () use ($option) {
                return $this->replaceInFile($option['search'], $option['replace'], config_path('jetstream.php'));
            });
        }
    }

    protected function copyAssets(): void
    {
        $this->components->info("Copying assets and related config files");
        $this->components->task("Css, js and stubs", function () {
            $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/css', resource_path('css'), '*.css');
            $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/js', resource_path('js'), '*.js');
            File::copyDirectory(__DIR__ . '/../../deploy/resources/stubs', resource_path('stubs'));
            return true;
        });
        $this->components->task("Images", function () {
            $this->copyFilesInDir(__DIR__ . '/../../deploy/assets/images', public_path('images'), '*.*');
            File::copyDirectory(__DIR__ . '/../../deploy/assets/images/graphical-menu', public_path('images/graphical-menu'));
            return true;
        });
        $this->components->task("Tailwind and vite config files", function () {
            copy(__DIR__.'/../../deploy/npm/tailwind.config.js', base_path('tailwind.config.js'));
            copy(__DIR__.'/../../deploy/npm/vite.config.js', base_path('vite.config.js'));
            return true;
        });
    }

    protected function customizeExceptionRendering(): void
    {
        $this->components->info("Customize exception rendering (invalid invitation links and expired login pages)");
        $this->components->task('Writing to bootstrap/app.php', function () {
            $bootstrapApp = file_get_contents(base_path('bootstrap/app.php'));
            $bootstrapApp = str_replace(
                '->withExceptions(function (Exceptions $exceptions) {',
                '->withExceptions(function (Exceptions $exceptions) {'
                .PHP_EOL."        \$exceptions->render(function (\Illuminate\Routing\Exceptions\InvalidSignatureException \$e, \Illuminate\Http\Request \$request) {"
                .PHP_EOL."            return response()->view('chimera::error.link-invalid', [], 403);"
                .PHP_EOL.'        });'
                .PHP_EOL."        \$exceptions->render(function (Throwable \$e, \Illuminate\Http\Request \$request) {"
                .PHP_EOL."            if (\$e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {"
                .PHP_EOL."                app('redirect')->setIntendedUrl(url()->previous());"
                .PHP_EOL."                return redirect()->route('login')"
                .PHP_EOL."                    ->withInput(request()->except('_token'))"
                .PHP_EOL."                    ->withErrors('Security token has expired. Please sign-in again.');"
                .PHP_EOL.'            }'
                .PHP_EOL.'        });'
                .PHP_EOL,
                $bootstrapApp,
            );
            file_put_contents(base_path('bootstrap/app.php'), $bootstrapApp);
        });
    }

    protected function installEnvFiles(): void
    {
        $this->components->info("Install new environment files");
        $this->components->task('.env', function () {
            return copy(__DIR__.'/../../deploy/.env.example', base_path('.env'));
        });
        $this->components->task('.env.example', function () {
            return copy(__DIR__.'/../../deploy/.env.example', base_path('.env.example'));
        });
        $this->components->task('Generate application key', function () {
            config(['app.key' => '']);
            return $this->callSilently('key:generate');
        });
    }

    protected function installEmptyWebRoutesFile(): void
    {
        $this->components->info("Install empty web routes file (web.php)");
        $this->components->task('Writing to routes/web.php', function () {
            (new Filesystem)->replace(base_path('routes/web.php'), "<?php" . PHP_EOL);
        });
    }

    protected function installJsDependencies(): void
    {
        $this->components->info("Npm packages");
        $this->components->task('Updating package.json', function () {
            $this->updateNodePackages(function ($packages) {
                return $this->requiredNodePackages + $packages;
            });
            return true;
        });
        $this->components->task('Running npm install & npm run build (takes time)', function () {
            $commands = ['npm install', 'npm run build'];
            $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);
            return $process->run();
        });
    }

    protected function requireComposerPackages(array $packages): int
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                //$this->output->write($output);
            });
    }

    protected function copyFilesInDir(string $srcDir, string $destDir, string $fileType = '*.php'): void
    {
        $fs = new Filesystem;
        $fs->ensureDirectoryExists($destDir);
        foreach (glob("$srcDir/$fileType") as $file) {
            $fs->copy($file, "$destDir/" . basename($file));
        }
    }

    protected static function updateNodePackages(callable $callback, $dev = true): false | int
    {
        if (! file_exists(base_path('package.json'))) {
            return false;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        return file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    protected static function flushNodeModules(): void
    {
        tap(new Filesystem, function ($files) {
            $files->deleteDirectory(base_path('node_modules'));
            $files->delete(base_path('yarn.lock'));
            $files->delete(base_path('package-lock.json'));
        });
    }

    protected function replaceInFile($search, $replace, $path): false | int
    {
        return file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
