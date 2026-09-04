<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class WalletResource extends JsonApiResource
{
    public function toAttributes(Request $request)
    {
        return [
            'owner_id' => $this->owner_id,
            'balance' => $this->balance,
            'status' => $this->status,
        ];
    }

    public function toRelationships(Request $request)
    {
        return [
            'owner' => UserResource::class,
            'ledgers' => LedgerResource::class,
            'transactions' => TransactionResource::class,
        ];
    }
}
