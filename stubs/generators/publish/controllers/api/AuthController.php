<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        $credentials = $request->only(['email', 'password']);

        if (! Auth::attempt($credentials)) {
            return response()->json(data: [
                'message' => 'These credentials do not match our records.',
            ], status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = $user->createToken('apiToken')->plainTextToken;

        return response()->json(data: [
            'message' => 'Successfully logged in',
            'token' => $token,
            'user' => $user,
        ], status: Response::HTTP_OK);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($request->password);
        unset($validated['password_confirmation']);

        $user = User::create($validated);
        $token = $user->createToken('apiToken')->plainTextToken;

        return response()->json(data: [
            'message' => 'Successfully registered',
            'token' => $token,
            'user' => $user,
        ], status: Response::HTTP_OK);
    }

    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return response()->json(data: ['message' => 'Successfully logged out'], status: Response::HTTP_OK);
    }

    public function me(): JsonResponse
    {
        return response()->json(data: [
            'message' => 'Authenticated',
            'user' => Auth::user(),
        ], status: Response::HTTP_OK);
    }
}
