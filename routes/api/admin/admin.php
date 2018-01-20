<?php

Route::group(['namespace' => 'API\admin', 'prefix' => 'admin', 'middleware' => ['client.credentials']], function () {

    // Usuarios
    Route::group(['middleware' => ['scope:superadmin']], function () {
        Route::resource('/usuario', 'UsuarioController');
        Route::resource('/comercio', 'ComercioController');
        // Tokens
        Route::resource('/usuario/{id}/token', 'UsuarioTokenController');
        Route::resource('/comercio/{uuid}/token', 'ComercioTokenController');
    });

//    // Tokens
//    Route::group(['middleware' => ['scope:superadmin']], function () {
//        Route::resource('/usuario/token', 'TokenController');
//    });
//

});
