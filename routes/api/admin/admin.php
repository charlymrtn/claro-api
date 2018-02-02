<?php

Route::group(['namespace' => 'API\admin', 'prefix' => 'admin', 'middleware' => ['client.credentials']], function () {

    // Usuarios
    Route::group(['middleware' => ['scope:superadmin']], function () {
        Route::apiResource('/usuario', 'UsuarioController');
        Route::apiResource('/comercio', 'ComercioController');
        // Tokens
        Route::apiResource('/usuario/{uid}/token', 'UsuarioTokenController');
        Route::delete('/comercio/{uuid}/token/{token}/revoke', 'ComercioTokenController@revoke')->name('token.revoke');
        Route::apiResource('/comercio/{uuid}/token', 'ComercioTokenController');
    });

});
