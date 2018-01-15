<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Auth;
use App\Models\User;

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
        // Prepara resultado
        $result = [
            'method' => __METHOD__,
            'result' => 'success',
            'user' => $oRequest->user(),
            'token' => $oRequest->user()->token(),
            'request_all' => $oRequest->all(),
        ];


        return response()->json($result);
    }

}
