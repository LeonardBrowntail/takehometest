<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class UserResource extends JsonApiResource
{
    public static $wrap = null;

    public function toAttributes(Request $request)
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }

    public function toRelationships(Request $request)
    {
        return [
            'wallet' => WalletResource::class,
        ];
    }
}
