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
    // @todo: Quitar permiso a: cliente-transacciones
    Route::group(['middleware' => ['scope:superadmin,cliente-transacciones,cliente-suscripciones']], function () {
        Route::apiResource('/plan', 'PlanController');
            Route::get('/plan/{uuid}/suscripciones', 'PlanController@suscripciones')->name('plan.suscripciones');
            Route::get('/plan/{uuid}/suscripciones/cancelar', 'PlanController@cancelarSuscripciones')->name('plan.suscripciones.cancelar');
            Route::get('/plan/{uuid}/cancelar', 'PlanController@cancelar')->name('plan.cancelar');
        Route::apiResource('/suscripcion', 'SuscripcionController');
            Route::get('/suscripcion/{uuid}/cancelar', 'SuscripcionController@cancelar')->name('suscripcion.cancelar');
    });

    // Clientes
    // @todo: Agregar permiso específico (ej: cliente-clientes)
    Route::group(['middleware' => ['scope:superadmin,cliente-transacciones']], function () {
        Route::apiResource('/cliente', 'ClienteController');
            Route::get('/cliente/{uuid}/tarjeta', 'ClienteController@tarjetas')->name('cliente.tarjetas');
            Route::get('/cliente/{uuid}/suscripcion', 'ClienteController@suscripciones')->name('cliente.suscripciones');
            Route::get('/cliente/id_externo/{id_externo}', 'ClienteController@showExterno')->name('cliente.id_externo');
    });
});