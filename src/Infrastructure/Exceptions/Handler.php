<?php

namespace App\Infrastructure\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
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
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Za API rute, uvek vraćaj JSON odgovor
        if ($request->is('api/*') || $request->expectsJson() || $request->wantsJson()) {
            // Validation exceptions
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validacione greške',
                    'errors' => $e->errors()
                ], 422);
            }

            // 404 Not Found
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resurs nije pronađen'
                ], 404);
            }

            // 405 Method Not Allowed
            if ($e instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metoda nije dozvoljena'
                ], 405);
            }

            // Default error response
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            
            $response = [
                'success' => false,
                'message' => $e->getMessage() ?: 'Došlo je do greške'
            ];

            // U development modu, dodaj detalje o grešci
            if (config('app.debug')) {
                $response['exception'] = get_class($e);
                $response['file'] = $e->getFile();
                $response['line'] = $e->getLine();
                $response['trace'] = $e->getTraceAsString();
            }

            return response()->json($response, $statusCode);
        }

        return parent::render($request, $e);
    }
}

