<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

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
     * Handle api exceptions globally.
     */    
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'errors' => null,
                ], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.',
                    'errors' => null,
                ], 403);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'errors' => null,
                ], 404);
            }

            if ($e instanceof TokenExpiredException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired.',
                    'errors' => null,
                ], 401);
            }

            if ($e instanceof TokenInvalidException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is invalid.',
                    'errors' => null,
                ], 401);
            }

            if ($e instanceof JWTException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication token is required.',
                    'errors' => null,
                ], 401);
            }

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => null,
            ], 500);

            // return response()->json([
            //     'success' => false,
            //     'message' => $e->getMessage(), // <-- Change this line to catch the real error
            //     'trace' => $e->getTraceAsString(), // <-- Optional: add this line to see the path trace
            //     'errors' => null
            // ], 500);
        }

        return parent::render($request, $e);
    }
}
