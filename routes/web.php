<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('index');
//});

// Auth
//Auth::routes();

// BBVA Pruebas
Route::any('/bbva', 'Bbva\BbvaController@index')->name('bbva');
Route::any('/bbva/batch/{fecha}', 'Bbva\BbvaController@batch')->name('bbva.batch');
// Fix temporal
Route::any('/fix', function() {

    // Borra índice email
    try {
        // Corrige problema de índices en tabla de clientes
        Illuminate\Support\Facades\Schema::connection('mysql_sa')->table('cliente', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropUnique('email');
        });
    } catch (\Exception $e) {
        echo "\n<br>El índice 'email' no existente. (" . $e->getMessage() . ")" ;
    }
    
    // Borra índice id_externo
    try {
        // Corrige problema de índices en tabla de clientes
        Illuminate\Support\Facades\Schema::connection('mysql_sa')->table('cliente', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropUnique('id_externo');
        });
    } catch (\Exception $e) {
        echo "\n<br>El índice 'id_externo' no existente. (" . $e->getMessage() . ")" ;
    }

    // Genera nuevos índices
    try {
        // Corrige problema de índices en tabla de clientes
        Illuminate\Support\Facades\Schema::connection('mysql_sa')->table('cliente', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->unique(['comercio_uuid', 'email'], 'comercio-email');
            $table->unique(['comercio_uuid', 'id_externo'], 'comercio-id');
        });
    } catch (\Exception $e) {
        echo "\n<br>Error al crear los nuevos índices: (" . $e->getMessage() . ")" ;
    }
});