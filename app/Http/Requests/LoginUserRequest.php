<?php

namespace App\Http\Requests;

use App\Rules\IndonesianPhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identity' => ['required', Rule::anyOf([['string', 'alpha_dash'], ['string', 'email'], ['string', new IndonesianPhoneNumber]])],
            'password' => ['required', 'string'],
        ];
    }
}
