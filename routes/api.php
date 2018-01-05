<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('client.credentials')->get('/v1', function (Request $request) {
//    return $request->user();
//});
Route::group(['namespace' => 'v1', 'prefix' => 'v1', 'middleware' => ['client.credentials']], function () {
    Route::resource('/cargo', 'API\v1\CargoController');
});
