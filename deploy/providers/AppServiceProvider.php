<?php

namespace App\Providers;

use App\Services\ConnectionLoader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
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
    }
}
