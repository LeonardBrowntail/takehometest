<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class TransactionResource extends JsonApiResource
{
    /**
     * The resource's relationships.
     */
    public $relationships = [
        'ledgers',
    ];

    public function toAttributes(Request $request)
    {
        return [
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
        ];
    }
}
