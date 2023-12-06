<?php

use App\Http\Controllers\Api\Panel\JarTransactionRequestController;
use App\Http\Controllers\Api\Panel\PanelCustomerController;
use App\Http\Controllers\Api\Panel\PanelGameController;
use App\Http\Controllers\Api\Panel\PanelJarTransactionController;
use Illuminate\Support\Facades\Route;

Route::controller(PanelCustomerController::class)->prefix('customers')->group(function () {
    Route::post('register', 'store');
    Route::post('login', 'login');
    Route::post('two-factory', 'twoFactory');
    Route::post('logout', 'logOut');

});

Route::group(['middleware' => ['auth:api', 'forbiddenExceptCustomer']], function () {

    Route::controller(PanelGameController::class)->prefix('games')->group(function () {
        Route::post('list', 'all');
    });

    Route::controller(PanelJarTransactionController::class)->prefix('jar/transactions')->group(function () {
        Route::post('/', 'myJarTransaction');
        Route::post('change/currency', 'changeCurrency');
    });


    Route::controller(JarTransactionRequestController::class)->prefix('jar/transactions/request')->group(function () {
        Route::post('', 'makeTransactionRequest');
        Route::post('/list', 'transactionRequests');
        Route::delete('/', 'deleteTransactionRequest');
    });


});
