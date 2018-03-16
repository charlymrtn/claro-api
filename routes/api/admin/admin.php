<?php

Route::group(['namespace' => 'API\admin', 'prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['client.credentials', 'scope:superadmin']], function () {

    // Usuarios
    Route::apiResource('/usuario', 'UsuarioController');
    Route::name('usuario.')->group(function () {
        Route::delete('/usuario/{uid}/token/{token}/revoke', 'UsuarioTokenController@revoke')->name('token.revoke');
        Route::apiResource('/usuario/{uid}/token', 'UsuarioTokenController');
    });
    // Comercios
    Route::apiResource('/comercio', 'ComercioController');
    Route::name('comercio.')->group(function () {
        Route::delete('/comercio/{uuid}/token/{token}/revoke', 'ComercioTokenController@revoke')->name('token.revoke');
        Route::apiResource('/comercio/{uuid}/token', 'ComercioTokenController');
    });
    //Ping
    Route::get('/ping', 'PingController@index');

});
