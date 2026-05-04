<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Identity\Application\Services\AuthService;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Http\Requests\RegisterRequest;
use App\Modules\Identity\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated(), $request);

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
        ], 'Registered successfully.', 201);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated(), $request);

        if (! $result) {
            return $this->error('Invalid credentials.', [], 401);
        }

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
        ], 'Logged in successfully.');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user(), $request);

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request)
    {
        return $this->success(new UserResource($request->user()), 'Authenticated user.');
    }
}
