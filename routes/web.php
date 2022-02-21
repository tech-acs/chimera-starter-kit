<?php

use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\FaqManagementController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SqlController;
use App\Http\Controllers\UsageStatsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified', 'log_page_views'])->group(function () {
    foreach (array_keys(config('chimera.pages') ?? []) as $page) {
        Route::get($page, [ChartsController::class, 'multi'])->name($page);
    }
    Route::get('{page}/single/{chart}', [ChartsController::class, 'single'])->name('single');

    Route::resource('sql', SqlController::class)->only(['create', 'store']);

    Route::get('faq', FaqController::class)->name('faq');

    Route::get('help', HelpController::class)->name('help');

    Route::middleware(['can:Super Admin'])->group(function () {
        Route::resource('roles', RoleController::class)->only(['index', 'store', 'edit', 'destroy']);
        Route::resource('users', UserController::class)->only(['index', 'edit', 'update', 'destroy']);
        Route::get('usage_stats', UsageStatsController::class)->name('usage_stats');
        Route::prefix('manage')->name('manage.')->group(function () {
            Route::resource('faq', FaqManagementController::class)->except(['show']);
        });
        Route::get('connection/{questionnaire}/test', [ConnectionController::class, 'test'])->name('connection.test');
        Route::resource('connection', ConnectionController::class);
        Route::get('setting', SettingController::class)->name('setting');
    });

    Route::fallback(function () {
        return redirect()->route('faq');
    });
});
