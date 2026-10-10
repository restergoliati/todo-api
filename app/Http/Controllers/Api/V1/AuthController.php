<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseApiController
{
    public function __construct(private AuthService $authService) 
    {
        $this->middleware('auth:api')->except(['register', 'login']);
    }

    
    public function register(
        RegisterRequest $request
    ): JsonResponse {
        $result = $this->authService->register(
            $request->validated()
        );

        return $this->success(
            $result,
            'Registration successful.',
            201
        );
    }

    public function login(
        LoginRequest $request
    ): JsonResponse {
        $result = $this->authService->login(
            $request->validated()
        );

        if (!$result) {
            return $this->error(
                'Invalid credentials.',
                401
            );
        }

        return $this->success(
            $result,
            'Login successful.'
        );
    }

    public function profile(): JsonResponse
    {
        return $this->success(
            auth('api')->user(),
            'Profile retrieved successfully.'
        );
    }
}