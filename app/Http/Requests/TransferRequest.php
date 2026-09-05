<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    protected function prepareForValidation()
    {
        if (! $this->user()->wallet) {
            throw ValidationException::withMessages(['destination_id' => 'Current user does not own a wallet.']);
        }
    }
}
