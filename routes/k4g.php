<?php

use App\Http\Controllers\Api\Marketplace\EnebaApiController;
use App\Http\Controllers\Api\Marketplace\GamivoApiController;
use App\Http\Controllers\Api\Marketplace\K4gApiController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['apiMiddleware', 'k4gMiddleware'], 'as' => 'k4g-no-api-doc.'], function () {
    Route::controller(K4gApiController::class)->group(function () {
        Route::post('reserve', 'reserve');
        Route::post('cancel', 'cancel');
        Route::post('dispatch', 'dispatch');
        Route::post('give', 'give');
        Route::post('buy', 'buy');
    });
});


