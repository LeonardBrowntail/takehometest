<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Services\MoneyService;
use App\Services\WalletService;
use App\Traits\WalletApiResponses;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;

class WalletController extends Controller
{
    use WalletApiResponses;

    public function show(Request $request)
    {
        $user = $request->user();
        if (! $user || ! ($user instanceof User)) {
            throw new AuthenticationException;
        }
        $wallet = $user->wallet;
        if (! $wallet) {
            throw new Exception("Current user's wallet is not found.");
        }

        return $this->showWalletResponse($wallet->toResource());
    }

    public function transfer(TransferRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        if (! $user || ! ($user instanceof User)) {
            throw new AuthenticationException;
        }
        $wallet = $user->wallet;
        if (! $wallet) {
            throw new Exception('Unable to find user\'s wallet');
        }
        $otherWallet = Wallet::find($validated['destination_id']);
        if (! $otherWallet) {
            throw new UnauthorizedException("Unable to find receiver wallet with id {$validated['destination_id']}");
        }
        $money = MoneyService::split((float) $validated['amount']);
        $transaction = WalletService::transfer($wallet, $otherWallet, $money['major'], $money['minor']);

        if (! $transaction) {
            throw new Exception('Transaction Failed');
        }

        return $this->transferWalletResponse($transaction->toResource());
    }

    public function deposit(DepositRequest $request)
    {
        $user = $request->user();
        if (! $user || ! ($user instanceof User)) {
            throw new AuthenticationException;
        }
        $wallet = $user->wallet;
        if (! $wallet) {
            throw new Exception("Unable to find user's wallet");
        }
        $money = MoneyService::split((float) $request->validated()['amount']);
        $transaction = WalletService::deposit($wallet, $money['major'], $money['minor']);
        if (! $transaction) {
            throw new Exception('Transaction failed');
        }

        return $this->depositWalletResponse($transaction->toResource());
    }
}
