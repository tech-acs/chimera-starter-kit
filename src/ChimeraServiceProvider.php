<?php

namespace Uneca\Chimera;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Laravel\Fortify\Fortify;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Livewire\Livewire;

use Uneca\Chimera\Services\ConnectionLoader;
use Uneca\Chimera\Services\PageBuilder;
use Uneca\Chimera\Services\Helpers;

class ChimeraServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $migrations = [
            'install_postgis_extension',
            'install_ltree_extension',
            'create_area_restrictions_table',
            'create_faqs_table',
            'create_invitations_table',
            'create_usage_stats_table',
            'create_areas_table',
            'create_pages_table',
            'create_questionnaires_table',
            'create_indicators_table',
            'create_indicator_page_table',
            'create_scorecards_table',
            'create_reports_table',
            'add_is_suspended_column_to_users_table',
            'create_notifications_table',
            'create_announcements_table',
            'create_reference_values_table',
            'create_area_hierarchies_table',
            'create_map_indicators_table',
            'create_analytics_table',
            'create_report_user_table',
            'add_case_stats_component_column_to_questionnaires_table',
            'add_driver_column_to_questionnaires_table',
        ];
        $package
            ->name('chimera')
            ->hasViews()
            ->hasViewComponents(
                'chimera',
                \Uneca\Chimera\Components\ChartCard::class,
                \Uneca\Chimera\Components\SimpleCard::class,
                \Uneca\Chimera\Components\Summary::class
            )
            ->hasConfigFile(['chimera', 'languages', 'filesystems'])
            ->hasTranslations()
            ->hasRoute('web')
            ->hasMigrations($migrations)
            ->hasCommands([
                \Uneca\Chimera\Commands\CacheIndicators::class,
                \Uneca\Chimera\Commands\CacheScorecards::class,
                \Uneca\Chimera\Commands\CacheCaseStats::class,
                \Uneca\Chimera\Commands\CacheMapIndicators::class,
                \Uneca\Chimera\Commands\CacheClear::class,
                \Uneca\Chimera\Commands\Chimera::class,
                \Uneca\Chimera\Commands\DataExport::class,
                \Uneca\Chimera\Commands\DataImport::class,
                \Uneca\Chimera\Commands\Dockerize::class,
                \Uneca\Chimera\Commands\Adminify::class,
                \Uneca\Chimera\Commands\Delete::class,
                \Uneca\Chimera\Commands\DownloadIndicatorTemplates::class,
                \Uneca\Chimera\Commands\GenerateReports::class,
                \Uneca\Chimera\Commands\MakeIndicator::class,
                \Uneca\Chimera\Commands\MakeMapIndicator::class,
                \Uneca\Chimera\Commands\MakeReport::class,
                \Uneca\Chimera\Commands\MakeScorecard::class,
                \Uneca\Chimera\Commands\Update::class,
                \Uneca\Chimera\Commands\Production::class,
                \Uneca\Chimera\Commands\UpdateIndicators::class
            ]);
    }

    public function packageRegistered()
    {
        Livewire::component('area-filter', \Uneca\Chimera\Http\Livewire\AreaFilter::class);
        Livewire::component('area-restriction-manager', \Uneca\Chimera\Http\Livewire\AreaRestrictionManager::class);
        Livewire::component('area-spreadsheet-importer', \Uneca\Chimera\Http\Livewire\AreaSpreadsheetImporter::class);
        Livewire::component('bulk-inviter', \Uneca\Chimera\Http\Livewire\BulkInviter::class);
        Livewire::component('chart', \Uneca\Chimera\Http\Livewire\Chart::class);
        Livewire::component('column-mapper', \Uneca\Chimera\Http\Livewire\ColumnMapper::class);
        Livewire::component('command-palette', \Uneca\Chimera\Http\Livewire\CommandPalette::class);
        Livewire::component('exporter', \Uneca\Chimera\Http\Livewire\Exporter::class);
        Livewire::component('invitation-manager', \Uneca\Chimera\Http\Livewire\InvitationManager::class);
        Livewire::component('language-switcher', \Uneca\Chimera\Http\Livewire\LanguageSwitcher::class);
        Livewire::component('map', \Uneca\Chimera\Http\Livewire\Map::class);
        Livewire::component('notification-bell', \Uneca\Chimera\Http\Livewire\NotificationBell::class);
        Livewire::component('notification-dropdown', \Uneca\Chimera\Http\Livewire\NotificationDropdown::class);
        Livewire::component('notification-inbox', \Uneca\Chimera\Http\Livewire\NotificationInbox::class);
        Livewire::component('reference-value-spreadsheet-importer', \Uneca\Chimera\Http\Livewire\ReferenceValueSpreadsheetImporter::class);
        Livewire::component('role-manager', \Uneca\Chimera\Http\Livewire\RoleManager::class);
        Livewire::component('case-stats', \Uneca\Chimera\Http\Livewire\CaseStats::class);
        Livewire::component('subscribe-to-report-notification', \Uneca\Chimera\Http\Livewire\SubscribeToReportNotification::class);
    }

    public function boot()
    {
        parent::boot();

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        (new ConnectionLoader())();

        Blade::if('connectible', function ($value) {
            try {
                DB::connection($value)->getPdo();
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

        Fortify::registerView(function (Request $request) {
            if (! $request->hasValidSignature()) {
                throw new InvalidSignatureException();
            }
            return view('auth.register')
                ->with(['encryptedEmail' => Crypt::encryptString($request->email)]);
        });

        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', \Uneca\Chimera\Http\Middleware\CheckAccountSuspension::class);
        $router->pushMiddlewareToGroup('web', \Uneca\Chimera\Http\Middleware\Language::class);
        $router->aliasMiddleware('enforce_2fa', \Uneca\Chimera\Http\Middleware\RedirectIf2FAEnforced::class);
        $router->aliasMiddleware('log_page_views', \Uneca\Chimera\Http\Middleware\LogPageView::class);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('chimera:generate-reports')->hourly();
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/stubs' => resource_path('stubs'),
            ], 'chimera-stubs');
        }
    }

    public function register()
    {
        parent::register();

        /*$this->app->when(Chart::class)
            ->needs(CachingInterface::class)
            ->give(IndicatorCaching::class);*/

        $this->app->bind('helpers', function ($app) {
            return new Helpers();
        });
    }
}
