<?php

namespace App\Http\Resources;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class LedgerResource extends JsonApiResource
{
    /**
     * The resource's relationships.
     */
    public $relationships = [
        'transaction',
        'wallet',
    ];

    public function toAttributes(Request $request)
    {
        return [
            'transaction_id' => $this->transaction_id,
            'wallet_id' => $this->wallet_id,
            'amount' => MoneyService::toDecimal((int) $this->amount),
            'type' => $this->type,
        ];
    }
}
