<?php

namespace App\Http\Resources;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class WalletResource extends JsonApiResource
{
    /**
     * The resource's relationships.
     */
    public $relationships = [
        'owner',
        'ledgers',
        'transactions',
    ];

    public function toAttributes(Request $request)
    {
        return [
            'owner_id' => $this->owner_id,
            'balance' => MoneyService::toDecimal($this->amount),
            'status' => $this->status,
        ];
    }
}
