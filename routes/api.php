<?php

use Illuminate\Support\Facades\Route;

Route::prefix('!/statamic-eyris')
    ->name('statamic-eyris.')
    ->group(function () {
        Route::middleware('statamic.cp')->get('login/{id}', [\Thoughtco\Eyris\Http\Controllers\LoginController::class, 'login'])->name('login');

        Route::middleware(\Thoughtco\Eyris\Http\Middleware\VerifyEyrisHeaders::class)
            ->group(function () {
                Route::post('login', [\Thoughtco\Eyris\Http\Controllers\LoginController::class, 'generateLink'])->name('generate-login');

                Route::post('update-environment', function () {
                    \Thoughtco\Eyris\Facades\Agent::updateEnvironment();

                    return response()->json(['success' => true]);
                })->name('update-environment');

            });
    });
