<?php

namespace App\Http\Requests;

use App\Rules\IndonesianPhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['string', 'alpha_dash', 'unique:users,username', 'min:3', 'required'],
            'email' => ['string', 'email:rfc', 'unique:users,email', 'required'],
            'phone' => ['string', new IndonesianPhoneNumber, 'unique:users,phone', 'required'],
            'password' => ['string', new Password(8), 'confirmed', 'required'],
        ];
    }
}
