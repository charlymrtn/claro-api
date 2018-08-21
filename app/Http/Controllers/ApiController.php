<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseAppController;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
     * Parse date fields from array
     *
     * @param  array $aArray
     * @param  array $aFieldNames Date field names to process
     * @return array
     */
    function parseArrayDates(array $aArray, array $aFieldNames): array
    {
        foreach ($aFieldNames as $sDateField) {
            if (!empty($aArray[$sDateField])) {
                $aArray[$sDateField] = Carbon::parse($aArray[$sDateField]);
            }
        }
        return $aArray;
    }

}
