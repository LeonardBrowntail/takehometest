<?php

namespace App\Traits;

trait AuthApiResponses
{
    use BaseApiResponse;

    private function RegisterResponse() {
        return $this->generalResponse(true, 'registered');
    }
    
    private function LoginResponse(string $token, mixed $user) {
        return $this->generalResponse(true, 'logged in', ['token' => $token, 'user' => $user]);
    }

    private function LogoutResponse() {
        return $this->generalResponse(true, 'logged out');
    }
}
