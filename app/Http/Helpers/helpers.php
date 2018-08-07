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
     * @param array $extraHeaders
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function ejsend_success($data, $status = 200, $extraHeaders = [])
    {
        $response = [
            'status' => 'success',
            'data' => $data,
            'http_code' => $status,
            'datetime' => Carbon\Carbon::now()->toRfc3339String(), // Cambio API 1.2.20180718 de toDateTimeString()
            'timestamp' => time(),
        ];
        return response()->json($response, $status, $extraHeaders);
    }
}

// Extended JSend error
// An error occurred in processing the request, i.e. an exception was thrown
// Required keys: status, data
if (!function_exists("ejsend_error")) {
    /**
     * @param $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function ejsend_error($error, $status = 500, $data = null, $extraHeaders = [])
    {
        $aResponse = [
            'status' => 'error',
            'error' => $error,
            'http_code' => $status,
            'datetime' => Carbon\Carbon::now()->toRfc3339String(), // Cambio API 1.2.20180718 de toDateTimeString()
            'timestamp' => time(),
        ];
        if ($data !== null) {
            $aResponse['data'] = $data;
        }
        return response()->json($aResponse, $status, $extraHeaders);
    }
}

// Extended JSend fail
// There was a problem with the data submitted, or some pre-condition of the API call wasn't satisfied
// Required keys: status, message   Optional keys: code, data
if (!function_exists("ejsend_fail")) {
    /**
     * @param $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function ejsend_fail($error, $status = 400, $data = null, $extraHeaders = [])
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
        if ($data !== null) {
            $aResponse['data'] = $data;
        }
        return response()->json($aResponse, $iHttpCode, $extraHeaders);
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
     * @return $array
     */
    function array_replace_keys(array $array, array $keys, $filter = false)
    {
        $aNew = [];
        foreach($array as $key => $value) {
            if(isset($keys[$key])) {
                $aNew[$keys[$key]] = $value;
            } elseif(!$filter) {
                $aNew[$key] = $value;
            }
        }
        return $aNew;
    }
}