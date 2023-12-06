<?php

use App\Http\Controllers\Api\Marketplace\KinguinApiController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['kinguinMiddleware', 'apiMiddleware'], 'as' => 'kinguin-no-api-doc.'], function () {
    Route::controller(KinguinApiController::class)->group(function () {
        Route::post('reserve', 'reserve');
        Route::post('give', 'give');
        Route::post('cancel', 'cancel');
        Route::post('delivered', 'delivery');
        Route::post('returned', 'return');
        Route::post('outofstock', 'outofstock');
        Route::get('exists/{productId}', 'ifExists');
    });
});
