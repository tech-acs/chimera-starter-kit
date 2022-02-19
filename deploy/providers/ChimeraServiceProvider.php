<?php

namespace App\Providers;

use App\Exceptions\Handler;
use App\Services\ConnectionLoader;
use App\Services\PageBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ChimeraServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        (new ConnectionLoader())();

        Blade::if('connectible', function ($value) {
            try {
                DB::connection($value);
                return true;
            } catch (\Exception $exception) {
                return false;
            }
        });

        Collection::macro('joinWithExternalColumn', function (array $keyValue, string $using, string $newColumnName) {
            return empty($keyValue) ?
                $this :
                $this->map(function ($item) use ($keyValue, $using, $newColumnName) {
                    if (property_exists($item, $using)) {
                        $item->$newColumnName = $keyValue[$item->$using] ?? null;
                    }
                    return $item;
                });
        });

        $pages = PageBuilder::pages();
        View::share('pages', $pages);
    }
}
