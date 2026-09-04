<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class TransactionResource extends JsonApiResource
{
    public function toAttributes(Request $request)
    {
        return [
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
        ];
    }

    public function toRelationships(Request $request)
    {
        return [
            'ledgers' => LedgerResource::class,
        ];
    }
}
