<?php

Route::group(['namespace' => 'API\admin', 'prefix' => 'admin', 'middleware' => ['client.credentials']], function () {

    // Usuarios
    Route::group(['middleware' => ['scope:superadmin']], function () {
        Route::resource('/usuario', 'UsuarioController');
    });

});
