<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\LoginException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Http\Resources\Auth\TokenResource;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function registration(RegistrationRequest $request)
    {
        $validated = $request->validated();
        $user = new User();
        $user->fill($validated);

        $user->save();
        $token = $user->createToken('defaultApiToken');
        return new TokenResource($token);
    }

    public function me()
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        if (!Auth::attempt($credentials)) {
            throw new LoginException();
        }

        $user = User::where('email', $request['email'])->first();
        $user->tokens()->delete();
        $token = $user->createToken('defaultApiToken');
        return new TokenResource($token);
    }
}
