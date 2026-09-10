<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;

class SanctumController extends Controller
{

    function __construct(private readonly AuthService $authService){}

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|max:255|confirmed'
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'data' => $this->authService->getUserWithToken($user)
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string'
        ]);
        $user = User::where('email', $request->email)->first();
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'status' => false,
                'message' => 'Email & Password does not match with our record.',
            ], 401);
        }
        return response()->json([
            'status' => true,
            'message' => 'User Logged In Successfully',
            'data' => $this->authService->getUserWithToken($user)
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'data' => $request->user()->only('id', 'name', 'email')
        ]);
    }


    public function refresh(Request $request)
    {
        $validatedData = $request->validate([
            'refresh_token' => 'required|string|max:64'
        ]);
        $hashedRefreshToken = $request->user()->refreshTokens()
            ->where('expires_at', '>', now())->first();

        if (!$hashedRefreshToken) {
            return response()->json([
                'message' => 'Invalid or expired refresh token'
            ], 401);
        }

        $isValid = Hash::check($validatedData['refresh_token'], $hashedRefreshToken['hash_token']);

        if (!$isValid) {
            return response()->json([
                'message' => 'Invalid or expired refresh token'
            ], 401);
        }

        return $this->authService->generateToken($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->refreshTokens()->delete();
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }



}
