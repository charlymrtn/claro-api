<?php

use Illuminate\Http\Request;

Route::get('/', function () {
    return view('index');
});

// API v1
require base_path('routes/api/v1/v1.php');
