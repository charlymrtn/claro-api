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
        // Formato de respuesta
        $aResponse = [
            'status' => 'fail',
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
