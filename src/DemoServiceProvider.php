<?php

namespace Uneca\Chimera;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Uneca\Chimera\Commands\DemoSetup;

class DemoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands(DemoSetup::class);
    }

    public function boot()
    {
        if (! config('chimera.demo_mode')) {
            return;
        }

        Fortify::loginView(fn () => view('chimera::demo.login'));

        $this->loadRoutesFrom(__DIR__.'/../../routes/demo.php');

        $this->app->make(ExceptionHandler::class)
            ->renderable(function (\Throwable $e, Request $request) {
                if ($e instanceof QueryException) {
                    return redirect()->back()->withInput()->with('flash', [
                        'bannerStyle' => 'warning',
                        'banner' => 'This is a demo site. Visitors cannot make changes.',
                    ]);
                }
            });

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('log_page_views', fn ($request, $next) => $next($request));
        $router->aliasMiddleware('enforce_2fa', fn ($request, $next) => $next($request));
        $router->aliasMiddleware('password.confirm', fn ($request, $next) => $next($request));
    }
}
