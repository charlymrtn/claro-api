<?php

use Illuminate\Http\Request;

Route::get('/', function () {
    return view('index');
});

Route::group(['middleware' => ['client.credentials']], function () {

    // API v1
    require base_path('routes/api/v1/v1.php');

    // API Admin
    require base_path('routes/api/admin/admin.php');

});

// PRUEBA BBVA
Route::resource('/bbva', 'Bbva\BbvaController');
