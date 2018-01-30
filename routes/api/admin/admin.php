<?php

Route::group(['namespace' => 'API\admin', 'prefix' => 'admin', 'middleware' => ['client.credentials']], function () {

    // Usuarios
    Route::group(['middleware' => ['scope:superadmin']], function () {
        Route::resource('/usuario', 'UsuarioController');
        Route::resource('/comercio', 'ComercioController');
        // Tokens
        Route::resource('/usuario/{uid}/token', 'UsuarioTokenController');
        Route::resource('/comercio/{uuid}/token', 'ComercioTokenController');
    });

});
