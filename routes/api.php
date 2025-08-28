<?php

use Illuminate\Support\Facades\Route;

Route::prefix('!/statamic-agency')
    ->middleware(\Thoughtco\StatamicAgency\Http\Middleware\VerifyAgencyHeaders::class)
    ->group(function () {
        Route::post('update-environment', function() {
            \Thoughtco\StatamicAgency\Facades\Agency::updateEnvironment();

            return response()->json(['success' => true]);
        });
    });
