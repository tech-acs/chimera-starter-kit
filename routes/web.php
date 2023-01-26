<?php

use Uneca\Chimera\Http\Controllers\ChartsController;
use Uneca\Chimera\Http\Controllers\FaqController;
use Uneca\Chimera\Http\Controllers\HelpController;
use Uneca\Chimera\Http\Controllers\HomeController;
use Uneca\Chimera\Http\Controllers\MapController;
use Uneca\Chimera\Http\Controllers\Manage\AnnouncementController;
use Uneca\Chimera\Http\Controllers\Manage\AreaController;
use Uneca\Chimera\Http\Controllers\Manage\AreaHierarchyController;
use Uneca\Chimera\Http\Controllers\Manage\ConnectionTestController;
use Uneca\Chimera\Http\Controllers\Manage\FaqManagementController;
use Uneca\Chimera\Http\Controllers\Manage\IndicatorController;
use Uneca\Chimera\Http\Controllers\Manage\AnalyticsController;
use Uneca\Chimera\Http\Controllers\Manage\MapIndicatorController;
use Uneca\Chimera\Http\Controllers\Manage\PageController;
use Uneca\Chimera\Http\Controllers\Manage\QuestionnaireController;
use Uneca\Chimera\Http\Controllers\Manage\ReferenceValueController;
use Uneca\Chimera\Http\Controllers\Manage\ReportManagementController;
use Uneca\Chimera\Http\Controllers\Manage\RoleController;
use Uneca\Chimera\Http\Controllers\Manage\SettingController;
use Uneca\Chimera\Http\Controllers\Manage\ScorecardController;
use Uneca\Chimera\Http\Controllers\Manage\UsageStatsController;
use Uneca\Chimera\Http\Controllers\Manage\UserController;
use Uneca\Chimera\Http\Controllers\Manage\UserSuspensionController;
use Uneca\Chimera\Http\Controllers\NotificationController;
use Uneca\Chimera\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('chimera::welcome');
})->name('landing');

Route::middleware(['web', 'auth:sanctum', 'verified', 'log_page_views', 'enforce_2fa'])->group(function () {
    Route::get('home', HomeController::class)->name('home');

    Route::get("page/{page:slug}", [ChartsController::class, 'page'])->name('page');
    Route::get('indicator/{indicator:slug}', [ChartsController::class, 'indicator'])->name('indicator');
    Route::get('map', MapController::class)->name('map');
    Route::get('report', [ReportController::class, 'index'])->name('report');
    Route::get('report/{report}/download', [ReportController::class, 'download'])->name('report.download');
    Route::get('report/{report}/generate', [ReportController::class, 'generate'])->name('report.generate');
    Route::get('faq', FaqController::class)->name('faq');
    Route::get('notification', NotificationController::class)->name('notification.index');

    Route::middleware(['can:Super Admin'])->prefix('manage')->group(function () {
        Route::resource('role', RoleController::class)->only(['index', 'store', 'edit', 'destroy']);
        Route::resource('user', UserController::class)->only(['index', 'edit', 'update']);
        Route::get('user/{user}/suspension', UserSuspensionController::class)->name('user.suspension');

        Route::prefix('developer')->name('developer.')->group(function () {
            Route::get('questionnaire/{questionnaire}/test-connection', ConnectionTestController::class)->name('questionnaire.connection.test');
            Route::resource('questionnaire', QuestionnaireController::class);
            Route::resource('area-hierarchy', AreaHierarchyController::class)->only(['index']);
            Route::resource('area', AreaController::class)->only(['index', 'edit', 'update']);
            Route::resource('reference-value', ReferenceValueController::class)->only(['index', 'edit', 'update']);
            if (app()->environment('local')) {
                Route::resource('area-hierarchy', AreaHierarchyController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
                Route::resource('area', AreaController::class)->only(['create', 'store']);
                Route::delete('area/truncate', [AreaController::class, 'destroy'])->name('area.destroy');
                Route::resource('reference-value', ReferenceValueController::class)->only(['create']);
                Route::delete('reference-value/truncate', [ReferenceValueController::class, 'destroy'])->name('reference-value.destroy');
            }
        });

        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        //Route::get('analyzable/{analyzable}/analytics', [AnalyticsController::class, 'show'])->name('analytics.show');
        Route::resource('page', PageController::class)->except(['show']);
        Route::resource('indicator', IndicatorController::class)->except(['show', 'create', 'store', 'destroy']);
        //Route::get('indicator/{indicator}/analytics', [AnalyticsController::class, 'show'])->name('analytics.show');
        Route::resource('scorecard', ScorecardController::class)->except(['show', 'create', 'store', 'destroy']);
        //Route::get('scorecard/{scorecard}/analytics', [AnalyticsController::class, 'show'])->name('analytics.show');
        Route::name('manage.')->group(function () {
            Route::resource('report', ReportManagementController::class)->except(['show']);
            Route::resource('map_indicator', MapIndicatorController::class)->except(['show']);
            Route::resource('faq', FaqManagementController::class)->except(['show']);
        });
        //Route::resource('setting', SettingController::class)->only(['index', 'edit', 'update']);
        Route::resource('announcement', AnnouncementController::class)->only(['index', 'create', 'store']);
        Route::get('usage_stats', UsageStatsController::class)->name('usage_stats');
    });

    Route::fallback(function () {
        return redirect()->route('home');
    });
});
