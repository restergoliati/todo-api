<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TodoController;
use App\Http\Controllers\Api\V1\JobController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public
    |--------------------------------------------------------------------------
    */

    // Route::prefix('auth')->group(function () {
    //     Route::post(
    //         '/register',
    //         [AuthController::class, 'register']
    //     );

    //     Route::post(
    //         '/login',
    //         [AuthController::class, 'login']
    //     );
    // });

    Route::middleware('throttle:auth')->group(function () {
        Route::post(
            '/auth/register',
            [AuthController::class, 'register']
        );

        Route::post(
            '/auth/login',
            [AuthController::class, 'login']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Protected
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:api')->group(function () {

        Route::get(
            '/profile',
            [AuthController::class, 'profile']
        );

        Route::post(
            '/todos/bulk-complete',
            [TodoController::class, 'bulkComplete']
        );

        Route::apiResource(
            'todos',
            TodoController::class
        )->except([
            'create',
            'edit',
        ]);

        Route::get(
            '/jobs/{id}',
            [JobController::class, 'show']
        );
    });


//     Route::middleware('auth:api')->group(function () {

//         Route::get(
//             '/profile',
//             [AuthController::class, 'profile']
//         );

//         Route::post(
//             '/todos/bulk-complete',
//             [TodoController::class, 'bulkComplete']
//         );

//         Route::apiResource(
//             'todos',
//             TodoController::class
//         )->except([
//             'create',
//             'edit',
//         ]);

//         Route::post(
//             '/todos/bulk-complete',
//             [TodoController::class, 'bulkComplete']
//         );

//         Route::get(
//             '/jobs/{id}',
//             [JobController::class, 'show']
//         );
//     });
});