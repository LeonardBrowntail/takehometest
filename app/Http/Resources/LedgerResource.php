<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class LedgerResource extends JsonApiResource
{
    public function toAttributes(Request $request)
    {
        return [
            'transaction_id' => $this->transaction_id,
            'wallet_id' => $this->wallet_id,
            'amount' => $this->amount,
            'type' => $this->type,
        ];
    }

    public function toRelationships(Request $request)
    {
        return [
            'transaction' => TransactionResource::class,
            'wallet' => WalletResource::class,
        ];
    }
}
