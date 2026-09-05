<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'destination_id' => ['required', 'uuid', 'exists:wallets,id', Rule::notIn($this->user()->wallet->id)],
            'amount' => ['required', 'decimal:2', 'min:1.00'],
        ];
    }
}
