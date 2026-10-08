<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'data' => $this->authService->userWithTokens($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->authenticate($request->safe()->only('email', 'password'));

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Email & Password does not match with our record.',
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'User Logged In Successfully',
            'data' => $this->authService->userWithTokens($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->only('id', 'name', 'email'),
        ]);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $tokens = $this->authService->refreshTokens(
            $request->user(),
            $request->validated('refresh_token')
        );

        if (! $tokens) {
            return response()->json([
                'message' => 'Invalid or expired refresh token',
            ], 401);
        }

        return response()->json($tokens);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->revokeTokens($request->user());

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
