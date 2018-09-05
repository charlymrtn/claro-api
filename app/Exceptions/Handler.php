<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof AuthorizationException) {
            return $this->forbidden($request, $e);
        } else if ($e instanceof MethodNotAllowedHttpException) {
            return $this->methodnotallowed($request, $e);
        } else if ($e instanceof NotFoundHttpException) {
            return $this->notfound($request, $e);
        } else if ($e instanceof ThrottleRequestsException) {
            return $this->tomanyrequests($request, $e);
        } else {
            return $this->unknown($request, $e);
            //return parent::render($request, $e);
        }
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $e): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_error(['code' => 401, 'type' => 'Autenticación', 'message' => 'No autenticado: ' . $e->getMessage()], 401);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbidden($request, AuthorizationException $e): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_error(['code' => 403, 'type' => 'Autenticación', 'message' => 'Prohibido: ' . $e->getMessage()], 403);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpKernel\Exception\NotFoundHttpException  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notfound($request, NotFoundHttpException $e): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_error(['code' => 404, 'type' => 'Sistema', 'message' => 'Contexto no encontrado: ' . $e->getMessage()], 404);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function methodnotallowed($request, MethodNotAllowedHttpException $e): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_error(['code' => 405, 'type' => 'Sistema', 'message' => 'Método no permitido: ' . $e->getMessage()], 405);
    }

    /**
     * Any other exception.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  \Illuminate\Http\Exceptions\ThrottleRequestsException  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function tomanyrequests($oRequest, ThrottleRequestsException $e): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_error(['code' => $e->getStatusCode(), 'type' => 'Sistema', 'message' => 'Ha excedido la tasa máxima de peticiones'], $e->getStatusCode(), null, $e->getHeaders());
    }

    /**
     * Any other exception.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  \Exception  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unknown($oRequest, Exception $e): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_exception($e, $e->getMessage() ?? 'Error desconocido', [], $e->getHeaders() ?? []);
    }
}
