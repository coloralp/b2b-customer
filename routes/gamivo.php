<?php

use App\Http\Controllers\Api\Marketplace\EnebaApiController;
use App\Http\Controllers\Api\Marketplace\GamivoApiController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['apiMiddleware', 'gamivoMiddleware'], 'as' => 'gamivo-no-api-doc.'], function () {
//Route::group(['middleware' => ['apiMiddleware', 'gamivoMiddleware']], function () {
    Route::controller(GamivoApiController::class)->group(function () {
        Route::post('reservation', 'reserve');
        Route::post('order', 'createOrder');

        Route::post('updateStock', 'updateStock');
    });
    Route::get('/', function () {//this used to check connection from kinguin
        return response()->json([
            'message' => 'Success!'
        ], \Illuminate\Http\Response::HTTP_OK);
    });

    Route::get('order/{id}/keys', [GamivoApiController::class, 'keys']);
});

Route::group(['middleware' => ['apiMiddleware']], function () {
    Route::get('/', function () {//this used to check connection from kinguin
        return response()->json([
            'message' => 'Success!'
        ], \Illuminate\Http\Response::HTTP_OK);
    });
});
