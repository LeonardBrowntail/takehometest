<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Traits\AuthApiResponses;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use AuthApiResponses;

    /**
     * Registration function.
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        return $this->RegisterResponse();
    }

    /**
     * Login function.
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
        $valid = $request->validated();

        $user = User::where('username', $valid['identity'])
            ->orWhere('email', $valid['identity'])
            ->orWhere('phone', $valid['identity'])
            ->first();

        if ($user && Hash::check($valid['password'], $user->password)) {
            // Revoke any existing token.
            $user->tokens()->delete();
            // Issue or re-issue a new token.
            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->LoginResponse($token, $user->toResource());
        } else {
            throw ValidationException::withMessages(['identity' => 'Invalid credentials, please recheck your fields and try again.']);
        }
    }

    /**
     * Logout function.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->LogoutResponse();
    }
}
