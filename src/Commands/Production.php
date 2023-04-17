<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Redis;
use Symfony\Component\Process\Process;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Models\ReferenceValue;
use Uneca\Chimera\Models\User;

class Production extends Command
{
    protected $signature = 'chimera:production-checklist';

    protected $description = 'In production, run a checklist of critical settings';

    public function handle()
    {
        $this->components->info('Running production environment checks');

        $this->components->task('Check env variables are set for production (APP_ENV=production & APP_DEBUG=false)', function () {
            $productionEnvValues = ['app.env' => 'production', 'app.debug' => false];
            return collect($productionEnvValues)
                ->map(function ($value, $key) {
                    return config($key) === $value;
                })
                ->reduce(function ($carry, $item) {
                    return $carry && $item;
                }, true);
        });

        $this->components->task('Check foundational data presence (area hierarchies, areas and reference values)', function () {
            try {
                $counts = [AreaHierarchy::count(), Area::count(), ReferenceValue::count()];
                return collect($counts)
                    ->reduce(function ($carry, $item) {
                        return $carry && ($item > 0);
                    }, true);
            } catch (\Throwable $throwable) {
                return false;
            }
        });

        $this->components->task('Check caching is functional and enabled (CACHE_DRIVER=redis, redis is reachable & CACHE_ENABLED=true)', function () {
            try {
                $productionEnvValues = ['cache.default' => 'redis', 'chimera.cache.enabled' => true];
                $redis = new Redis();
                $redis->connect(config('database.redis.cache.host'), config('database.redis.cache.port'));
                $username = config('database.redis.cache.username');
                $password = config('database.redis.cache.password');
                if (isset($username) && isset($password)) {
                    $redis->auth([$username, $password]);
                }
                $redisReachable = (bool)$redis->ping();
                return collect($productionEnvValues)
                    ->map(function ($value, $key) {
                        return config($key) === $value;
                    })
                    ->merge(['redis.reachable' => $redisReachable])
                    ->reduce(function ($carry, $item) {
                        return $carry && $item;
                    }, true);
            } catch (\Throwable $throwable) {
                return false;
            }
        });

        $this->components->task('Check source databases are configured and reachable', function () {
            try {
                $connections = Questionnaire::active()->pluck('name');
                if ($connections->isEmpty()) {
                    return false;
                }
                return $connections
                    ->reduce(function ($carry, $connection) {
                        try {
                            DB::connection($connection)->getPDO();
                            $connectible = true;
                        } catch (\Throwable $throwable) {
                            $connectible = false;
                        }
                        return $carry && $connectible;
                    }, true);
            } catch (\Throwable $throwable) {
                return false;
            }
        });

        $this->components->task('Check email has been properly configured and is sending', function () {
            try {
                Mail::raw('This is a test email from the dashboard', function($msg) {
                    $msg->to(User::first()->email ?? 'admin@example.com')
                        ->subject('Test Email');
                });
                return true;
            } catch (\Throwable $throwable) {
                return false;
            }
        });

        $this->components->task('Check queue manager (supervisord) is running', function () {
            try {
                $response = Http::get('http://127.0.0.1:9001');
                return $response->ok();
            } catch (\Throwable $throwable) {
                return false;
            }
        });

        $this->components->task('Check public/storage has been linked to storage/app/public', function () {
            try {
                return (new Filesystem())->exists(public_path('storage'));
            } catch (\Throwable $throwable) {
                return false;
            }
        });

        $this->components->task('Check dashboard is running in secure/https mode (SECURE=true)', function () {
            return config('chimera.secure');
        });

        $this->components->task('Check storage and bootstrap/cache folders are writable', function () {
            try {
                // substr(sprintf('%o', fileperms('storage')), -4);
                // posix_getpwuid(fileowner('storage'));
                return is_writable('storage') && is_writable('bootstrap/cache');
            } catch (\Throwable $throwable) {
                return false;
            }
        });

        $this->newLine();

        return Command::SUCCESS;
    }
}
