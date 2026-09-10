<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{

    function __construct(private readonly AuthService $authService)
    {
    }

    function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    function callback()
    {
        $status = 200;
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::whereEmail($googleUser->getEmail())->first();
        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
            ]);
            $status = 201;
        }

        return response()->json([
            'data' => $this->authService->getUserWithToken($user)
        ], $status);
    }

}
