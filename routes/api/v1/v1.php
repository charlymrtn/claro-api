<?php

Route::group(['namespace' => 'API\v1', 'prefix' => 'v1', 'as' => 'api.v1.', 'middleware' => ['client.credentials']], function () {

    // API v1 Principal
    Route::any('/', 'APIv1Controller@index')->name('default');

    // Tarjetas
    Route::group(['middleware' => ['scope:superadmin,cliente-tarjetas']], function () {
        Route::resource('/tarjeta', 'TarjetaController');
    });

    // Cargos
    Route::group(['middleware' => ['scope:superadmin,cliente-transacciones']], function () {
        Route::resource('/cargo', 'CargoController');
    });

});
