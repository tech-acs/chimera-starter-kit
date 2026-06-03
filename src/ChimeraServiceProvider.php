<?php

namespace Uneca\Chimera;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;
use Opcodes\LogViewer\Facades\LogViewer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Uneca\Chimera\Commands\Adminify;
use Uneca\Chimera\Commands\CacheCaseStats;
use Uneca\Chimera\Commands\CacheClear;
use Uneca\Chimera\Commands\CacheIndicators;
use Uneca\Chimera\Commands\CacheMapIndicators;
use Uneca\Chimera\Commands\CacheScorecards;
use Uneca\Chimera\Commands\Chimera;
use Uneca\Chimera\Commands\ChimeraArtefactGenerator;
use Uneca\Chimera\Commands\CustomJetstreamInstallCommand;
use Uneca\Chimera\Commands\DataExport;
use Uneca\Chimera\Commands\DataImport;
use Uneca\Chimera\Commands\Delete;
use Uneca\Chimera\Commands\DeployHorizon;
use Uneca\Chimera\Commands\Dockerize;
use Uneca\Chimera\Commands\ExportAreas;
use Uneca\Chimera\Commands\GenerateReports;
use Uneca\Chimera\Commands\MakeGauge;
use Uneca\Chimera\Commands\MakeIndicator;
use Uneca\Chimera\Commands\MakeMapIndicator;
use Uneca\Chimera\Commands\MakeQueryFragment;
use Uneca\Chimera\Commands\MakeReferenceValueSynthesizer;
use Uneca\Chimera\Commands\MakeReport;
use Uneca\Chimera\Commands\MakeScorecard;
use Uneca\Chimera\Commands\Production;
use Uneca\Chimera\Commands\TransferReferenceValues;
use Uneca\Chimera\Commands\Update;
use Uneca\Chimera\Components\ChartCard;
use Uneca\Chimera\Components\SmartTable;
use Uneca\Chimera\Components\Summary;
use Uneca\Chimera\Http\Middleware\CheckAccountSuspension;
use Uneca\Chimera\Http\Middleware\HorizonAccess;
use Uneca\Chimera\Http\Middleware\Language;
use Uneca\Chimera\Http\Middleware\LogPageView;
use Uneca\Chimera\Http\Middleware\RedirectIf2FAEnforced;
use Uneca\Chimera\Livewire\AreaFilter;
use Uneca\Chimera\Livewire\AreaInsightsFilter;
use Uneca\Chimera\Livewire\AreaRestrictionManager;
use Uneca\Chimera\Livewire\AreaSpreadsheetImporter;
use Uneca\Chimera\Livewire\ArtisanRunner;
use Uneca\Chimera\Livewire\BulkInviter;
use Uneca\Chimera\Livewire\CacheClearer;
use Uneca\Chimera\Livewire\CaseStats;
use Uneca\Chimera\Livewire\Chart;
use Uneca\Chimera\Livewire\ColumnMapper;
use Uneca\Chimera\Livewire\CommandPalette;
use Uneca\Chimera\Livewire\Exporter;
use Uneca\Chimera\Livewire\GaugeComponent;
use Uneca\Chimera\Livewire\IndicatorTester;
use Uneca\Chimera\Livewire\InvitationManager;
use Uneca\Chimera\Livewire\LanguageSwitcher;
use Uneca\Chimera\Livewire\LevelAreaNameDisplay;
use Uneca\Chimera\Livewire\LiveSearch;
use Uneca\Chimera\Livewire\Map;
use Uneca\Chimera\Livewire\NotificationBell;
use Uneca\Chimera\Livewire\NotificationDropdown;
use Uneca\Chimera\Livewire\NotificationInbox;
use Uneca\Chimera\Livewire\ReferenceValueSpreadsheetImporter;
use Uneca\Chimera\Livewire\RoleManager;
use Uneca\Chimera\Livewire\SpecialSectionBorder;
use Uneca\Chimera\Livewire\SubscribeToReportNotification;
use Uneca\Chimera\Livewire\UserPageSizeAdjuster;
use Uneca\Chimera\Livewire\XRay;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Setting;
use Laravel\Mcp\Facades\Mcp;
use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
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
                ChartCard::class,
                Summary::class,
                SmartTable::class
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
                'add_for_column_to_pages_table',
                'add_scope_column_to_indicators_table',
                'add_scope_column_to_scorecards_table',
            ])
            ->hasCommands([
                CacheIndicators::class,
                CacheScorecards::class,
                CacheCaseStats::class,
                CacheMapIndicators::class,
                CacheClear::class,
                Chimera::class,
                DataExport::class,
                DataImport::class,
                Dockerize::class,
                Adminify::class,
                Delete::class,
                GenerateReports::class,
                MakeIndicator::class,
                MakeMapIndicator::class,
                MakeReport::class,
                MakeScorecard::class,
                MakeGauge::class,
                Update::class,
                Production::class,
                CustomJetstreamInstallCommand::class,
                MakeQueryFragment::class,
                DeployHorizon::class,
                MakeReferenceValueSynthesizer::class,
                TransferReferenceValues::class,
                ChimeraArtefactGenerator::class,
                ExportAreas::class,
            ]);
    }

    public function packageRegistered()
    {
        Livewire::component('area-filter', AreaFilter::class);
        Livewire::component('area-insights-filter', AreaInsightsFilter::class);
        Livewire::component('area-restriction-manager', AreaRestrictionManager::class);
        Livewire::component('area-spreadsheet-importer', AreaSpreadsheetImporter::class);
        Livewire::component('bulk-inviter', BulkInviter::class);
        Livewire::component('chart', Chart::class);
        Livewire::component('column-mapper', ColumnMapper::class);
        Livewire::component('command-palette', CommandPalette::class);
        Livewire::component('exporter', Exporter::class);
        Livewire::component('invitation-manager', InvitationManager::class);
        Livewire::component('language-switcher', LanguageSwitcher::class);
        Livewire::component('map', Map::class);
        Livewire::component('notification-bell', NotificationBell::class);
        Livewire::component('notification-dropdown', NotificationDropdown::class);
        Livewire::component('notification-inbox', NotificationInbox::class);
        Livewire::component('reference-value-spreadsheet-importer', ReferenceValueSpreadsheetImporter::class);
        Livewire::component('role-manager', RoleManager::class);
        Livewire::component('case-stats', CaseStats::class);
        Livewire::component('subscribe-to-report-notification', SubscribeToReportNotification::class);
        Livewire::component('indicator-tester', IndicatorTester::class);
        Livewire::component('special-section-border', SpecialSectionBorder::class);
        Livewire::component('artisan-runner', ArtisanRunner::class);
        Livewire::component('cache-clearer', CacheClearer::class);
        Livewire::component('x-ray', XRay::class);
        Livewire::component('gauge', GaugeComponent::class);
        Livewire::component('live-search', LiveSearch::class);
        Livewire::component('level-area-name-display', LevelAreaNameDisplay::class);
        Livewire::component('user-page-size-adjuster', UserPageSizeAdjuster::class);
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
            return session('developer_mode_enabled', false) || $this->app->environment('local');
        });

        LogViewer::auth(function ($request) {
            return session('developer_mode_enabled', false);
        });

        (new ConnectionLoader)();

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
                throw new InvalidSignatureException;
            }

            return view('auth.register')
                ->with(['encryptedEmail' => Crypt::encryptString($request->email)]);
        });

        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', CheckAccountSuspension::class);
        $router->pushMiddlewareToGroup('web', Language::class);
        $router->aliasMiddleware('enforce_2fa', RedirectIf2FAEnforced::class);
        $router->aliasMiddleware('log_page_views', LogPageView::class);
        $router->aliasMiddleware('horizon', HorizonAccess::class);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('chimera:generate-reports')->hourly();
        });

        Mcp::local('dashboard-artefact-generator', DashboardArtefactGenerator::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/stubs' => resource_path('stubs'),
            ], 'chimera-stubs');
        }

        $this->app->singleton('settings', function () {
            try {
                if (Schema::hasTable('settings')) {
                    return Cache::rememberForever('settings', fn () => Setting::all()->pluck('value', 'key'));
                }
            } catch (\Exception) {
                //
            }

            return collect();
        });

        $settings = app('settings');
        if ($settings->isNotEmpty() && settings('mail_enabled', false)) {
            config([
                'mail.default' => 'chimera_smtp',
                'mail.mailers.chimera_smtp' => [
                    'transport' => 'smtp',
                    'host' => settings('mail_host'),
                    'port' => (int) settings('mail_port'),
                    'encryption' => settings('mail_encryption'),
                    'username' => settings('mail_username'),
                    'password' => settings('mail_password'),
                ],
                'mail.from.address' => settings('mail_from_address'),
                'mail.from.name' => settings('mail_from_name'),
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
