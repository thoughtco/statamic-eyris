<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/statamic-agency')
    ->middleware(\Thoughtco\StatamicAgency\Http\Middleware\VerifyAgencyHeaders::class)
    ->group(function () {
        // tbc
    });
