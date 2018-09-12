<?php

namespace App\Http\Controllers;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller as BaseAppController;
use App\Models\User;

class ApiController extends BaseAppController
{
    /**
     * Valida estructura JSON
     *
     * @param  string   $sJson
     * @param  bool     $bThrowExceptions
     * @return void
     */
    public function validateJson(string $sJson)
    {
        if (!empty($sJson) && empty(json_decode($sJson))) {
            throw new \Exception('Estructura JSON inválida.', 400);
        }
    }

    /**
     * Obtiene usuario autenticado por token
     *
     * @return User
     */
    public function getApiUser(): User
    {
        return Auth::user();
    }

    /**
     * Parsea fechas con Carbon
     *
     * @param  array $aArray
     * @param  array $aFieldNames Date field names to process
     * @return array
     */
    public function parseArrayDates(array $aArray, array $aFieldNames): array
    {
        foreach ($aFieldNames as $sDateField) {
            if (!empty($aArray[$sDateField])) {
                $aArray[$sDateField] = Carbon::parse($aArray[$sDateField]);
            }
        }
        return $aArray;
    }

    /**
     * Prepara, mapea, sanitiza y valida request
     *
     * @param mixed $oRequest Clase de tipo Request o subclase
     * @param mixed $oModel Modelo del objeto a crear o actualizar.
     * @param array $aLabelMap Arreglo con mapa de etiquetas
     *
     * @return array Arreglo campos mapeados, sanitzados y validados
     */
    public function preparaRequest($oRequest, $oModel, array $aLabelMap = [])
    {
        // Prepara arreglo con request
        $aRequest = $oRequest->all();
        // Intercambia labels por campos reales
        if (!empty($aLabelMap)) {
            $aRequest = array_replace_keys($oRequest->all(), array_flip($aLabelMap));
        }
        // Filtra campos actualizables
        if ($oRequest->method() == 'PUT') {
            if (!empty($oModel->updatable)) {
                $aRequest = array_only($aRequest, $oModel->updatable);
            }
        }
        // Sanitiza datos
        // @todo: Inserta métodos de sanitización
        // Prepara reglas de validación
        $aRules = $oModel->rules;
        if ($oRequest->method() == 'PUT') {
            $aRules = array_only($oModel->rules, array_keys($aRequest));
        }
        // Valida request
        $oValidator = Validator::make($aRequest, $aRules, $oModel->validation_messages);
        if ($oValidator->fails()) {
            throw new ValidationException($oValidator);
        }
        return $aRequest;
    }
}
