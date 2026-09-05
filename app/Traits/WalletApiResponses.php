<?php

namespace App\Traits;

trait WalletApiResponses
{
    use BaseApiResponse;

    private function showWalletResponse(mixed $data)
    {
        return $this->generalResponse(true, 'fetched wallet data', $data);
    }

    private function depositWalletResponse(mixed $transaction)
    {
        return $this->generalResponse(true, 'deposited', $transaction);
    }

    private function transferWalletResponse(mixed $transaction)
    {
        return $this->generalResponse(true, 'transfered', $transaction);
    }
}
