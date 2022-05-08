<?php

use App\Http\Controllers\ChartsController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Manage\ConnectionTestController;
use App\Http\Controllers\Manage\FaqManagementController;
use App\Http\Controllers\Manage\IndicatorController;
use App\Http\Controllers\Manage\PageController;
use App\Http\Controllers\Manage\QuestionnaireController;
use App\Http\Controllers\Manage\RoleController;
use App\Http\Controllers\Manage\SettingController;
use App\Http\Controllers\Manage\StatController;
use App\Http\Controllers\Manage\UsageStatsController;
use App\Http\Controllers\Manage\UserController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ReportsController;
use App\Services\PageBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified', 'log_page_views'])->group(function () {

    Route::get('home', HomeController::class)->name('home');

    Route::get("page/{slug}", [ChartsController::class, 'page'])->name('page');

    Route::get('indicator/{slug}', [ChartsController::class, 'indicator'])->name('indicator');

    Route::get('map', MapController::class)->name('map');

    Route::get('reports', ReportsController::class)->name('reports');

    Route::get('faq', FaqController::class)->name('faq');

    Route::get('help', HelpController::class)->name('help');

    Route::middleware(['can:Super Admin'])->prefix('manage')->group(function () {
        Route::resource('role', RoleController::class)->only(['index', 'store', 'edit', 'destroy']);
        Route::resource('user', UserController::class)->only(['index', 'edit', 'update', 'destroy']);

        Route::resource('page', PageController::class)->except(['show']);
        Route::resource('indicator', IndicatorController::class)->except(['show', 'create', 'store', 'destroy']);
        Route::resource('stat', StatController::class)->except(['show', 'create', 'store', 'destroy']);

        Route::resource('setting', SettingController::class)->only(['index', 'edit', 'update']);
        Route::get('usage_stats', UsageStatsController::class)->name('usage_stats');
        Route::name('manage.')->group(function () {
            Route::resource('faq', FaqManagementController::class)->except(['show']);
        });
        Route::get('questionnaire/{questionnaire}/test-connection', [ConnectionTestController::class, 'test'])->name('questionnaire.connection.test');
        Route::resource('questionnaire', QuestionnaireController::class);
    });

    Route::fallback(function () {
        return redirect()->route('faq');
    });
});
