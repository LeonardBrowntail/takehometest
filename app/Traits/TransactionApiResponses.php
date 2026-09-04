<?php

namespace App\Traits;

trait TransactionApiResponses
{
    use BaseApiResponse;

    public function showTransactionsResponse(mixed $data)
    {
        return $this->generalResponse(true, 'fetched transactions', $data);
    }
}
