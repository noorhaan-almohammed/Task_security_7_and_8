<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
        /**
     * This is a custom exception handler method that renders responses for exceptions
     * @param mixed $request: Represents the HTTP request that caused the exception. It allows us to access request data if needed.
     * @param \Throwable $exception : Represents the caught exception that occurred during the request processing.
     * @return mixed|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // Check if the exception is an instance of ModelNotFoundException
        if ($exception instanceof ModelNotFoundException) {

            return response()->json([
                // 'data' is set to null because the requested resource was not found
                'data' => null,
                'message' => 'Not found',
            ], 404);
        }
        if ($exception instanceof HttpException) {
            return response()->json([
                'message' => 'You are not authorized .'
            ], $exception->getStatusCode());
        }
        if ($exception instanceof QueryException && $exception->getCode() === '23000') {
            return response()->json(['message' => 'This email is already taken.'], 400);
        }
        // If the exception is not a ModelNotFoundException, call the parent render method
        return parent::render($request, $exception);
    }
}
