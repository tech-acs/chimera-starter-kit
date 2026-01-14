<?php

namespace Uneca\Chimera;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Laravel\Fortify\Fortify;
use Opcodes\LogViewer\Facades\LogViewer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Livewire\Livewire;

use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Setting;
use Uneca\Chimera\Services\ConnectionLoader;
use Uneca\Chimera\Services\PageBuilder;

class ChimeraServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('chimera')
            ->hasConfigFile(['chimera', 'languages', 'filesystems', 'logging'])
            ->hasViews()
            ->hasViewComponents(
                'chimera',
                \Uneca\Chimera\Components\ChartCard::class,
                \Uneca\Chimera\Components\Summary::class,
                \Uneca\Chimera\Components\SmartTable::class
            )
            ->hasTranslations()
            ->hasRoute('web')
            ->publishesServiceProvider('HorizonServiceProvider')
            ->hasMigrations([
                'install_postgis_extension',
                'install_ltree_extension',
                'create_area_restrictions_table',
                'create_invitations_table',
                'create_usage_stats_table',
                'create_areas_table',
                'create_pages_table',
                'create_data_sources_table',
                'create_indicators_table',
                'create_pageables_table',
                'create_scorecards_table',
                'create_reports_table',
                'create_notifications_table',
                'create_announcements_table',
                'create_reference_values_table',
                'create_area_hierarchies_table',
                'create_map_indicators_table',
                'create_analytics_table',
                'create_report_user_table',
                'create_settings_table',
                'add_is_suspended_and_last_login_at_columns_to_users_table',
                'create_chart_templates_table',
                'create_inapplicables_table',
                'create_gauges_table',
            ])
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
                \Uneca\Chimera\Commands\GenerateReports::class,
                \Uneca\Chimera\Commands\MakeIndicator::class,
                \Uneca\Chimera\Commands\MakeMapIndicator::class,
                \Uneca\Chimera\Commands\MakeReport::class,
                \Uneca\Chimera\Commands\MakeScorecard::class,
                \Uneca\Chimera\Commands\MakeGauge::class,
                \Uneca\Chimera\Commands\Update::class,
                \Uneca\Chimera\Commands\Production::class,
                \Uneca\Chimera\Commands\CustomJetstreamInstallCommand::class,
                \Uneca\Chimera\Commands\MakeQueryFragment::class,
                \Uneca\Chimera\Commands\DeployHorizon::class,
                \Uneca\Chimera\Commands\MakeReferenceValueSynthesizer::class,
                \Uneca\Chimera\Commands\TransferReferenceValues::class,
                \Uneca\Chimera\Commands\ChimeraArtefactGenerator::class,
            ]);
    }

    public function packageRegistered()
    {
        Livewire::component('area-filter', \Uneca\Chimera\Livewire\AreaFilter::class);
        Livewire::component('area-insights-filter', \Uneca\Chimera\Livewire\AreaInsightsFilter::class);
        Livewire::component('area-restriction-manager', \Uneca\Chimera\Livewire\AreaRestrictionManager::class);
        Livewire::component('area-spreadsheet-importer', \Uneca\Chimera\Livewire\AreaSpreadsheetImporter::class);
        Livewire::component('bulk-inviter', \Uneca\Chimera\Livewire\BulkInviter::class);
        Livewire::component('chart', \Uneca\Chimera\Livewire\Chart::class);
        Livewire::component('column-mapper', \Uneca\Chimera\Livewire\ColumnMapper::class);
        Livewire::component('command-palette', \Uneca\Chimera\Livewire\CommandPalette::class);
        Livewire::component('exporter', \Uneca\Chimera\Livewire\Exporter::class);
        Livewire::component('invitation-manager', \Uneca\Chimera\Livewire\InvitationManager::class);
        Livewire::component('language-switcher', \Uneca\Chimera\Livewire\LanguageSwitcher::class);
        Livewire::component('map', \Uneca\Chimera\Livewire\Map::class);
        Livewire::component('notification-bell', \Uneca\Chimera\Livewire\NotificationBell::class);
        Livewire::component('notification-dropdown', \Uneca\Chimera\Livewire\NotificationDropdown::class);
        Livewire::component('notification-inbox', \Uneca\Chimera\Livewire\NotificationInbox::class);
        Livewire::component('reference-value-spreadsheet-importer', \Uneca\Chimera\Livewire\ReferenceValueSpreadsheetImporter::class);
        Livewire::component('role-manager', \Uneca\Chimera\Livewire\RoleManager::class);
        Livewire::component('case-stats', \Uneca\Chimera\Livewire\CaseStats::class);
        Livewire::component('subscribe-to-report-notification', \Uneca\Chimera\Livewire\SubscribeToReportNotification::class);
        Livewire::component('indicator-tester', \Uneca\Chimera\Livewire\IndicatorTester::class);
        Livewire::component('special-section-border', \Uneca\Chimera\Livewire\SpecialSectionBorder::class);
        Livewire::component('artisan-runner', \Uneca\Chimera\Livewire\ArtisanRunner::class);
        Livewire::component('cache-clearer', \Uneca\Chimera\Livewire\CacheClearer::class);
        Livewire::component('x-ray', \Uneca\Chimera\Livewire\XRay::class);
        Livewire::component('gauge', \Uneca\Chimera\Livewire\GaugeComponent::class);
        Livewire::component('live-search', \Uneca\Chimera\Livewire\LiveSearch::class);
        Livewire::component('level-area-name-display', \Uneca\Chimera\Livewire\LevelAreaNameDisplay::class);
    }

    public function packageBooted()
    {
        Gate::before(function ($user, $ability) {
            if ($ability === 'developer-mode') {
                return null;
            }
            return $user->hasRole('Super Admin') ? true : null;
        });

        Gate::define('developer-mode', function ($user) {
            return (session('developer_mode_enabled', false) || $this->app->environment('local'));
        });

        LogViewer::auth(function ($request) {
            return session('developer_mode_enabled', false);
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
        $router->aliasMiddleware('horizon', \Uneca\Chimera\Http\Middleware\HorizonAccess::class);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('chimera:generate-reports')->hourly();
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/stubs' => resource_path('stubs'),
            ], 'chimera-stubs');
        }

        $this->app->singleton('settings', function () {
            if (Schema::hasTable('settings')) {
                return Cache::rememberForever('settings', fn() => Setting::all()->pluck('value', 'key'));
            } else {
                return collect();
            }
        });

        $settings = app('settings');
        if ($settings->isNotEmpty() && settings('mail_enabled', false)) {
            config([
                'mail.default'   => 'chimera_smtp',
                'mail.mailers.chimera_smtp' => [
                    'transport'  => 'smtp',
                    'host'       => settings('mail_host'),
                    'port'       => (int) settings('mail_port'),
                    'encryption' => settings('mail_encryption'),
                    'username'   => settings('mail_username'),
                    'password'   => settings('mail_password'),
                ],
                'mail.from.address' => settings('mail_from_address'),
                'mail.from.name'    => settings('mail_from_name'),
            ]);
        }

        $this->app->singleton('hierarchies', function () {
            if (Schema::hasTable('area_hierarchies')) {
                return AreaHierarchy::orderBy('index')->pluck('name');
            } else {
                return collect();
            }
        });
    }
}
