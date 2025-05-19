<?php

namespace App\Exceptions;

use App\Http\Traits\Response as ResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseTrait;
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

    public function render($request, Throwable $exception)
    {
        // dd($exception);
        $errorMessage = $exception->getMessage();

        if ($exception instanceof QueryException) {
            return $this->errorResponse('Database error occurred. ' . $exception->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->errorResponse($this->responseMessage("Authentication!", "unauthentication"), null, 401);
        } else {
                return $this->errorResponse(
                    $errorMessage,
                    [],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }
    }
}
