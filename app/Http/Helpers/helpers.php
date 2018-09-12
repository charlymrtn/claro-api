<?php

/**
 * JSON JSend response Helpers
 */

// Extended JSend Success
// All went well.
// Required keys: status, data
if (!function_exists("ejsend_success")) {
    /**
     * @param $data
     * @param int $status HTTP status code
     * @param array $aExtraHeaders
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function ejsend_success($data, $status = 200, $aExtraHeaders = [])
    {
        $response = [
            'status' => 'success',
            'data' => $data,
            'http_code' => $status,
            'datetime' => Carbon\Carbon::now()->toRfc3339String(), // Cambio API 1.2.20180718 de toDateTimeString()
            'timestamp' => time(),
        ];
        return response()->json($response, $status, $aExtraHeaders);
    }
}

// Extended JSend error
// An error occurred in processing the request, i.e. an exception was thrown
// Required keys: status, data
if (!function_exists("ejsend_error")) {
    /**
     * @param $error
     * @param int $status HTTP status code
     * @param $data
     * @param array $aExtraHeaders
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function ejsend_error($error, $status = 500, $data = null, $aExtraHeaders = [])
    {
        $aResponse = [
            'status' => 'error',
            'error' => $error,
            'http_code' => $status,
            'datetime' => Carbon\Carbon::now()->toRfc3339String(), // Cambio API 1.2.20180718 de toDateTimeString()
            'timestamp' => time(),
        ];
        if (!empty($data)) {
            $aResponse['data'] = $data;
        }
        return response()->json($aResponse, $status, $aExtraHeaders);
    }
}

// Extended JSend fail
// There was a problem with the data submitted, or some pre-condition of the API call wasn't satisfied
// Required keys: status, message   Optional keys: code, data
if (!function_exists("ejsend_fail")) {
    /**
     * @param $error
     * @param int $status HTTP status code
     * @param $data
     * @param array $aExtraHeaders
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function ejsend_fail($error, $status = 400, $data = null, $aExtraHeaders = [])
    {
        // Formato de variables
        $iHttpCode = (int) $status;
        // Formato de respuesta
        $aResponse = [
            'status' => 'fail',
            'error' => $error,
            'http_code' => $iHttpCode,
            'datetime' => Carbon\Carbon::now()->toRfc3339String(), // Cambio API 1.2.20180718 de toDateTimeString()
            'timestamp' => time(),
        ];
        if (!empty($data)) {
            $aResponse['data'] = $data;
        }
        return response()->json($aResponse, $iHttpCode, $aExtraHeaders);
    }
}

// Extended JSend error from exception
if (!function_exists("ejsend_exception")) {
    /**
     * @param int $status HTTP status code
     * @param string $sMensaje Mensaje de error general.
     * @param array $aErrores Arreglo de errores.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    function ejsend_exception(\Exception $e, $sMensaje = '', $aErrores = [], $aExtraHeaders = [])
    {
        // Define mensaje de error
        if (empty($sMensaje)) {
            $sMensaje = $e->getMessage();
        }
        // Define http status code
        $iHttpStatusCode = (int) $e->getCode();
        if (!empty($e->getStatusCode)) {
            $iHttpStatusCode = $e->getStatusCode();
        } elseif ($iHttpStatusCode < 100 || $iHttpStatusCode > 600) {
            $iHttpStatusCode = 500;
            $sMensaje = 'Error interno de aplicación.';
        }
        // Define tipo de error
        $sErrorType = 'General';
        if (empty($iHttpStatusCode)) {
            $sErrorType = 'Sistema';
        } elseif ($iHttpStatusCode >= 300 && $iHttpStatusCode <= 399) {
            $sErrorType = 'Redirección';
        } elseif ($iHttpStatusCode >= 400 && $iHttpStatusCode <= 499) {
            $sErrorType = 'Petición';
        } elseif ($iHttpStatusCode >= 500 && $iHttpStatusCode <= 599) {
            $sErrorType = 'Sistema';
        }
        // Define arreglo de errores
        if (!empty($aErrores)) {
            $aErrores = ['errors' => $aErrores];
        }
        // Regresa jsend de error
        return ejsend_error(['code' => $e->getCode(), 'type' => $sErrorType, 'message' => $sMensaje], $iHttpStatusCode, $aErrores, $aExtraHeaders);
    }
}

// Array filter nulls
if (!function_exists("array_filter_null")) {
    /**
     * Remove null elements from array
     *
     * @param  array   $array
     *
     * @return $array
     */
    function array_filter_null(array $array)
    {
        foreach($array as $key => $value) {
            if(is_null($value)) {
                unset($array[$key]);
            }
        }
        return $array;
    }
}

// Array replace keys
if (!function_exists("array_replace_keys")) {
    /**
     * Replace keys of given array by values of $keys
     * $keys format is [$oldKey => $newKey]
     *
     * With $filter == true, will remove elements with key not in $keys
     *
     * @param  array   $array
     * @param  array   $keys
     * @param  boolean $filter
     *
     * @return array
     */
    function array_replace_keys(array $array, array $keys, $filter = false)
    {
        $aNewArray = [];
        foreach($array as $key => $value) {
            if(isset($keys[$key])) {
                $aNewArray[$keys[$key]] = $value;
            } elseif(!$filter) {
                $aNewArray[$key] = $value;
            }
        }
        return $aNewArray;
    }
}

// Array undot
if (!function_exists("array_undot")) {
    /**
     * Expands a single level array with dot notation into a multi-dimensional array
     *
     * @param  array   $aDotArray
     *
     * @return array
     */
    function array_undot(array $aDotArray)
    {
        $aNewArray = [];
        foreach ($aDotArray as $key => $value) {
            array_set($aNewArray, $key, $value);
        }
        return $aNewArray;
    }
}


// Json request structure validation
if (!function_exists("validate_json")) {
    /**
     * Validate a json string structure
     *
     * @param  string   $sJson
     * @param  bool     $bThrowExceptions
     *
     * @return bool
     */
    function validate_json(string $sJson, bool $bThrowExceptions = true): bool
    {
        if (!empty($sJson) && empty(json_decode($sJson))) {
            if ($bThrowExceptions) {
                throw new \Exception('Estructura JSON inválida.', 400);
            }
            return false;
        }
        return true;
    }
}
