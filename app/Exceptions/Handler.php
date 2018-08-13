<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return $this->forbidden($request, $exception);
        } else if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->methodnotallowed($request, $exception);
        } else if ($exception instanceof NotFoundHttpException) {
            return $this->notfound($request, $exception);
        } else {
            return parent::render($request, $exception);
        }
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_error(['code' => 401, 'type' => 'Autenticación', 'message' => 'No autenticado: ' . $exception->getMessage()], 401);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbidden($request, AuthorizationException $exception): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_error(['code' => 403, 'type' => 'Autenticación', 'message' => 'Prohibido: ' . $exception->getMessage()], 403);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpKernel\Exception\NotFoundHttpException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notfound($request, NotFoundHttpException $exception): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_error(['code' => 404, 'type' => 'Sistema', 'message' => 'Contexto no encontrado: ' . $exception->getMessage()], 404);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function methodnotallowed($request, MethodNotAllowedHttpException $exception): JsonResponse
    {
        // API sólo responde con JSONs
        return ejsend_error(['code' => 405, 'type' => 'Sistema', 'message' => 'Método no permitido: ' . $exception->getMessage()], 405);
    }
}
