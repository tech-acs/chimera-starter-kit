<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class Chimera extends Command
{
    public $signature = 'chimera:install {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    public $description = 'Install the Dashboard Starter Kit into your newly created Laravel application';

    private function copyFilesInDir(string $srcDir, string $destDir, string $fileType = '*.php')
    {
        $fs = new Filesystem;
        $fs->ensureDirectoryExists($destDir);
        foreach (glob("$srcDir/$fileType") as $file) {
            $fs->copy($file, "$destDir/" . basename($file));
        }
    }

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

    protected static function flushNodeModules()
    {
        tap(new Filesystem, function ($files) {
            $files->deleteDirectory(base_path('node_modules'));

            $files->delete(base_path('yarn.lock'));
            $files->delete(base_path('package-lock.json'));
        });
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

    private function installJetstream()
    {
        $this->callSilent('jetstream:install', ['stack' => 'livewire']);
        $this->callSilent('vendor:publish', ['--tag' => 'jetstream-views']);
    }

    private function copyJetstreamModifications()
    {
        $this->copyFilesInDir(__DIR__ . '/../../deploy/jetstream-modifications/actions', app_path('Actions/Fortify'));

        copy(__DIR__.'/../../deploy/jetstream-modifications/views/register.blade.php', resource_path('views/auth/register.blade.php'));
        copy(__DIR__.'/../../deploy/jetstream-modifications/views/app.blade.php', resource_path('views/layouts/app.blade.php'));
        copy(__DIR__.'/../../deploy/jetstream-modifications/views/guest.blade.php', resource_path('views/layouts/guest.blade.php'));
        copy(__DIR__.'/../../deploy/jetstream-modifications/views/navigation-menu.blade.php', resource_path('views/navigation-menu.blade.php'));
        copy(__DIR__.'/../../deploy/jetstream-modifications/views/show.blade.php', resource_path('views/profile/show.blade.php'));
        copy(__DIR__.'/../../deploy/jetstream-modifications/views/area-restriction.blade.php', resource_path('views/profile/area-restriction.blade.php'));
    }

    private function publishResources()
    {
        $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/css', resource_path('css'), '*.css');
        $this->copyFilesInDir(__DIR__ . '/../../deploy/resources/js', resource_path('js'), '*.js');
        File::copyDirectory(__DIR__ . '/../../deploy/resources/stubs', resource_path('stubs'));

        $this->copyFilesInDir(__DIR__ . '/../../deploy/assets/images', public_path('images'), '*.*');

        copy(__DIR__.'/../../deploy/npm/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__.'/../../deploy/npm/vite.config.js', base_path('vite.config.js'));
    }

    private function editConfigFiles()
    {
        // enable: profile photo and terms + privacy | disable: account deletion
        $this->replaceInFile('// Features::profilePhotos(),', 'Features::profilePhotos(),', config_path('jetstream.php'));
        $this->replaceInFile('// Features::termsAndPrivacyPolicy(),', 'Features::termsAndPrivacyPolicy(),', config_path('jetstream.php'));
        $this->replaceInFile('Features::accountDeletion(),', '// Features::accountDeletion(),', config_path('jetstream.php'));

        // Make timezone setable from .env
        $this->replaceInFile("'timezone' => 'UTC'", "'timezone' => env('APP_TIMEZONE', 'UTC')", config_path('app.php'));

        // Set the User model to be used
        $this->replaceInFile("'model' => App\Models\User::class", "'model' => Uneca\Chimera\Models\User::class", config_path('auth.php'));
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
            'spatie/simple-excel:^2.5',
            'spatie/laravel-translatable:^6.1',
            'spatie/db-dumper:^3.3',
            'gasparesganga/php-shapefile:^3.4'
        ]);

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

        /*$this->callSilent('queue:batches-table');
        $this->comment('Job batches migration generated');*/

        copy(__DIR__.'/../../deploy/web.php', base_path('routes/web.php'));
        $this->comment('Copied empty route file (web.php)');

        $this->editConfigFiles();
        $this->comment('Updated app, auth, and jetstream (enable: profile photo and terms + privacy | disable: account deletion) config files');

        // Exception handler (for token mismatch and invalid invitation exceptions) [??? try to not replace the file! Find a way!]
        copy(__DIR__.'/../../deploy/Handler.php', app_path('Exceptions/Handler.php'));

        $this->updateNodePackages(function ($packages) {
            return [
                "leaflet" => "^1.9.3",
                "plotly.js-basic-dist-min" => "^2.17.1",
                "plotly.js-locales" => "^2.17.1",
                "@alpinejs/focus" => "3.10.5",
                "@tailwindcss/line-clamp" => "^0.4.2"
            ] + $packages;
        });
        $this->comment('Updated package.json with required npm packages');

        $this->info('All done');
        $this->newLine();

        return self::SUCCESS;
    }

}
