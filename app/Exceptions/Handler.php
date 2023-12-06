<?php

namespace App\Exceptions;

use App\Traits\ApiTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ApiTrait;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

        $this->renderable(function (InternalException $exception) {
            $response = [
                'error' => 1,
                'message' => $exception->getMessage(),
                'code' => $exception->getInternalCode(),
                'description' => $exception->getDescription(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ];

            return response()->json($response);
        });
    }

    public function render($request, Throwable $exception)
    {
        //return $this->exceptionThrowable($exception);

        return parent::render($request, $exception);
    }


}
