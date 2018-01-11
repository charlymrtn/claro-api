<?php

Route::group(['namespace' => 'API\v1', 'prefix' => 'v1', 'middleware' => ['client.credentials']], function () {
#Route::group(['namespace' => 'API\v1', 'prefix' => 'v1', 'middleware' => []], function () {
#Route::group(['namespace' => 'API\v1', 'prefix' => 'v1', 'middleware' => ['client.credentials', 'permission:accesar api']], function () {

    // API v1 Principal
    Route::any('/', 'APIv1Controller@index')->name('api.v1');

//    // Tarjetas
//    Route::group(['middleware' => ['permission:listar tarjetas']], function () {
//        Route::resource('/', 'TarjetaController');
//    });
//    // Cargos
//    Route::group(['middleware' => ['permission:listar tarjetas']], function () {
//        Route::resource('/', 'CargoController');
//    });

});
