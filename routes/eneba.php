<?php

use App\Http\Controllers\Api\Marketplace\EnebaApiController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['enebaMiddleware', 'apiMiddleware'], 'as' => 'eneba-no-api-doc.'], function () {
    Route::controller(EnebaApiController::class)->group(function () {
        Route::post('reservation', 'reserve');
        Route::post('provision', 'provide');
        Route::post('cancellation', 'cancel');
    });
});
