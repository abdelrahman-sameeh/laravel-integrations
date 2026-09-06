<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{

    function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string'
        ]);
        $token = auth()->attempt($credentials);
        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
        return $this->respondWithToken($token);

    }

    function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|min:2|max:50',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|max:50|confirmed'
        ]);
        $user = new User();
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = bcrypt($validatedData['password']);
        $user->save();

        $token = auth()->login($user);

        return $this->respondWithToken($token, 201);
    }

    function me(Request $request)
    {
        return [
            'user' => auth()->user()->only('id', 'name', 'email')
        ];
    }

    function refresh()
    {
        try {
            $newToken = auth()->refresh();
            auth()->setToken($newToken);
            return $this->respondWithToken($newToken);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Refresh token has expired, please login again'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is not provided'], 401);
        }
    }


    function logout()
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout, please try again'], 500);
        }
    }

    protected function respondWithToken($token, $status = 200)
    {
        return response()->json([
            'user' => auth()->user()->only(['id', 'name', 'email']),
            'token' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expire_in' => auth()->factory()->getTTL() * 60,
            ]
        ], $status);
    }


}
