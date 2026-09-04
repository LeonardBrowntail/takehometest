<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\TransactionApiResponses;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use TransactionApiResponses;

    public function show(Request $request)
    {
        $user = $request->user();
        if (! $user || ! ($user instanceof User)) {
            throw new AuthenticationException;
        }

        $wallet = $user->wallet;
        if (! $wallet) {
            throw new Exception("Unable to find user's wallet.");
        }

        $transactions = $wallet->transactions;

        return $this->showTransactionsResponse($transactions->toResourceCollection());
    }
}
