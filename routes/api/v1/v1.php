<?php

Route::group(['namespace' => 'API\v1', 'prefix' => 'v1', 'as' => 'api.v1.'], function () {

    // API v1 Principal
    Route::any('/', 'APIv1Controller@index')->name('default');

    // API Tarjetas
    Route::group(['middleware' => ['scope:superadmin,cliente-tarjetas']], function () {
        Route::apiResource('/tarjeta', 'TarjetaController');
    });

    // API Cargos
    Route::group(['middleware' => ['scope:superadmin,cliente-transacciones']], function () {
        Route::post('/cargo', 'CargoController@cargo')->name('cargo.cargo');
        Route::post('/cargo/{uuid}/cancelar', 'CargoController@cancel')->name('cargo.cancelar');
        Route::post('/cargo/{uuid}/reembolsar', 'CargoController@refund')->name('cargo.reembolsar');
    });

    // API Suscripciones
    // @todo: Cambiar permiso a: cliente-suscripciones
    Route::group(['middleware' => ['scope:superadmin,cliente-transacciones']], function () {
        Route::apiResource('/plan', 'PlanController');
    });

    // Clientes
    Route::group(['middleware' => ['scope:superadmin']], function () {
        Route::apiResource('/cliente', 'ClienteController');
    });
});