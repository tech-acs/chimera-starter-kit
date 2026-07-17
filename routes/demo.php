<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::post('login-demo', function () {
    $user = (new (Config::get('auth.providers.users.model')))
        ->where('email', config('chimera.demo_email'))
        ->first();

    if (! $user) {
        return redirect()->route('login')->with('flash', [
            'bannerStyle' => 'danger',
            'banner' => 'Demo user not found. Run php artisan chimera:demo-setup first.',
        ]);
    }

    Auth::login($user);

    return redirect()->intended('home');
})->name('login.demo')->middleware('web');
