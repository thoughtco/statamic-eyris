<?php

use Illuminate\Support\Facades\Route;

Route::prefix('!/statamic-agency')
    ->name('statamic-agency.')
    ->group(function () {
        Route::middleware('statamic.cp')->get('login/{id}', [\Thoughtco\StatamicAgency\Http\Controllers\LoginController::class, 'login'])->name('login');

        Route::middleware(\Thoughtco\StatamicAgency\Http\Middleware\VerifyAgencyHeaders::class)
            ->group(function () {
                Route::post('login', [\Thoughtco\StatamicAgency\Http\Controllers\LoginController::class, 'generateLink'])->name('generate-login');

                Route::post('update-environment', function () {
                    \Thoughtco\StatamicAgency\Facades\Agency::updateEnvironment();

                    return response()->json(['success' => true]);
                })->name('update-environment');

            });
    });
