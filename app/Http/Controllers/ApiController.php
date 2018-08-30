<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseAppController;
use App\Models\User;

class ApiController extends BaseAppController
{
    /**
     * Validate Json structure
     *
     * @param  string   $sJson
     * @param  bool     $bThrowExceptions
     * @return void
     */
    function validateJson(string $sJson)
    {
        if (!empty($sJson) && empty(json_decode($sJson))) {
            throw new \Exception('Estructura JSON inválida.', 400);
        }
    }

    /**
     * Get token authenticated user
     *
     * @return User
     */
    function getApiUser(): User
    {
        return Auth::user();
    }

    /**
     * Parse date fields from request
     *
     * @param  Illuminate\Http\Request $oRequest Request
     * @param  array $aFieldNames Date field names to process
     * @return array
     */
    function parseRequestDates(Request $oRequest, array $aFieldNames): array
    {
        $aResults = [];
        foreach ($aFieldNames as $sDateField) {
            if (!empty($oRequest->input($sDateField))) {
                $aResults[$sDateField] = Carbon::parse($oRequest->input($sDateField));
            }
        }
        return $aResults;
    }
}
