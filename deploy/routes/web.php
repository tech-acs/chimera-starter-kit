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
use App\Http\Controllers\Manage\UsageStatsController;
use App\Http\Controllers\Manage\UserController;
use App\Services\PageBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified', 'log_page_views'])->group(function () {

    Route::get('home', HomeController::class)->name('home');

    foreach (array_keys(PageBuilder::pages() ?? []) as $page) {
        Route::get($page, [ChartsController::class, 'multi'])->name($page);
    }
    Route::get('{page}/single/{chart}', [ChartsController::class, 'single'])->name('single');

    Route::get('faq', FaqController::class)->name('faq');

    Route::get('help', HelpController::class)->name('help');

    Route::middleware(['can:Super Admin'])->group(function () {
        Route::resource('role', RoleController::class)->only(['index', 'store', 'edit', 'destroy']);
        Route::resource('user', UserController::class)->only(['index', 'edit', 'update', 'destroy']);

        Route::resource('page', PageController::class)->except(['show']);
        Route::resource('indicator', IndicatorController::class)->except(['show', 'create', 'store', 'destroy']);

        Route::get('usage_stats', UsageStatsController::class)->name('usage_stats');
        Route::prefix('manage')->name('manage.')->group(function () {
            Route::resource('faq', FaqManagementController::class)->except(['show']);
        });
        Route::get('questionnaire/{questionnaire}/test-connection', [ConnectionTestController::class, 'test'])->name('questionnaire.connection.test');
        Route::resource('questionnaire', QuestionnaireController::class);
    });

    Route::fallback(function () {
        return redirect()->route('faq');
    });
});
