<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class APIv1Controller extends Controller
{

    public function __construct()
    {
        //
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        echo '{"result":"success"}';
    }

}
