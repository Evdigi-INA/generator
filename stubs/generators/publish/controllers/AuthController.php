<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{LoginRequest, RegisterRequest};
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{Auth, Hash};
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'These credentials do not match our records.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = $user->createToken('apiToken')->plainTextToken;

        return response()->json([
            'message' => 'Successfully logged in',
            'token' => $token,
            'user' => $user,
        ], Response::HTTP_OK);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        unset($validated['password_confirmation']);

        $validated['password'] = Hash::make($request->password);

        $user = User::create($validated);

        $token = $user->createToken('apiToken')->plainTextToken;

        return response()->json([
            'message' => 'Successfully registered',
            'token' => $token,
            'user' => $user,
        ], Response::HTTP_OK);
    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out'], Response::HTTP_OK);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'message' => 'Authenticated',
            'user' => auth()->user(),
        ], Response::HTTP_OK);
    }
}
