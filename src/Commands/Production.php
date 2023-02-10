<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Redis;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Models\ReferenceValue;

class Production extends Command
{
    protected $signature = 'chimera:production {action=check}';

    protected $description = 'In production, check critical settings or apply critical settings';

    public function handle()
    {
        $action = $this->argument('action');
        if ($action === 'check') {
            $this->components->info('Running production environment checks');

            $this->components->task('Ensure env variables are set for production (APP_ENV=production & APP_DEBUG=false)', function () {
                $productionEnvValues = ['app.env' => 'production', 'app.debug' => false];
                return collect($productionEnvValues)
                    ->map(function ($value, $key) {
                        return config($key) === $value;
                    })
                    ->reduce(function ($carry, $item) {
                        return $carry && $item;
                    }, true);
            });

            $this->components->task('Ensure foundational data presence (area hierarchies, areas and reference values)', function () {
                $counts = [AreaHierarchy::count(), Area::count(), ReferenceValue::count()];
                return collect($counts)
                    ->reduce(function ($carry, $item) {
                        return $carry && ($item > 0);
                    }, true);
            });

            $this->components->task('Ensure caching is functional and enabled (CACHE_DRIVER=redis, redis is reachable & CACHE_ENABLED=true)', function () {
                $productionEnvValues = ['cache.default' => 'redis', 'chimera.cache.enabled' => true];
                $redis = new Redis();
                $redis->connect(config('database.redis.cache.host'), config('database.redis.cache.port'));
                $redisReachable = (bool)$redis->ping();
                return collect($productionEnvValues)
                    ->map(function ($value, $key) {
                        return config($key) === $value;
                    })
                    ->merge(['redis.reachable' => $redisReachable])
                    ->reduce(function ($carry, $item) {
                        return $carry && $item;
                    }, true);
            });

            $this->components->task('Ensure source databases are configured and reachable', function () {
                $connections = Questionnaire::active()->pluck('name');
                if ($connections->isEmpty()) {
                    return false;
                }
                return $connections
                    ->reduce(function ($carry, $connection) {
                        try {
                            DB::connection($connection)->getPDO();
                            $connectible = true;
                        } catch (\Exception $exception) {
                            $connectible = false;
                        }
                        return $carry && $connectible;
                    }, true);
            });

            $this->components->task('Ensure email has been properly configured', function () {
                try {
                    //Mail::send();
                    return true;
                } catch (\Exception $exception) {
                    return false;
                }
            });

            $this->components->task('Ensure queue manager (supervisord) is running', function () {
                $response = Http::get('http://127.0.0.1:9001');
                return $response->ok();
            });

            $this->components->task('Ensure public/storage has been linked to storage/app/public', function () {

            });

            $this->components->task('Ensure dashboard is running in secure/https mode (SECURE=true)', function () {
                return config('chimera.secure');
            });

            $this->components->task('Ensure schedules are in place (laravel cron and then for caches)', function () {
                Artisan::call('schedule:list');
                $output = trim(Artisan::output());

            });

            $this->components->task('Ensure storage and bootstrap/cache folders have correct permissions', function () {
                // is_writable()
                // substr(sprintf('%o', fileperms('storage')), -4)
                // posix_getpwuid(fileowner('storage'));
            });

            $this->newLine();

        } elseif ($action === 'deploy') {
            // supervisor conf
        } else {

        }

        return Command::SUCCESS;
    }
}
