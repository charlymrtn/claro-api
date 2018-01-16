<?php

namespace app\Http\Controllers\API\admin;

use Log;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class UsuarioController extends Controller
{

//        $user = User::find(1);
//        $token = $user->createToken('API Personal Access Token', ['superadmin'])->accessToken;


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
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }
}
