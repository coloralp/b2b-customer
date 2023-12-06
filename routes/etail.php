<?php


use App\Http\Controllers\Api\Marketplace\EtailApiController;

Route::group(['middleware' => ['etailMiddleware', 'apiMiddleware']], function () {
    Route::controller(EtailApiController::class)->group(function (){
        Route::post('search','search');
        Route::post('detail','detail');
        Route::post('order/create','createOrder');
    });
});
