<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\Transaction;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Str;

class WalletService
{
    /**
     * Securely adds fund to a wallet.
     *
     * @param  Wallet  $wallet  Wallet to be filled.
     * @param  float  $amount  Amount of funds to add.
     */
    public static function addBalance(Wallet $wallet, float $amount): ?Transaction
    {
        // create new deposit transaction attempt entry.
        $transaction = Transaction::create([
            'type' => 'deposit',
            'status' => 'pending',
            'description' => 'pending deposit',
        ]);
        if (! $transaction) {
            throw new Exception('unable to create a new transaction.');
        }

        try {
            return DB::transaction(function () use ($wallet, $amount, $transaction) {
                if ($amount <= 0) {
                    throw ValidationException::withMessages(['amount' => 'Amount must not be less than or equal to 0.']);
                }
                if ($wallet->status != 'available') {
                    throw new UnauthorizedException('Wallet is either frozen or suspended.', 401);
                }

                // create new ledger.
                Ledger::create([
                    'transaction_id' => $transaction->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $amount,
                ]);

                $newNonce = (string) Str::uuid();
                $updated = Wallet::find($wallet->id)->whereNonce($wallet->nonce)->update([
                    'balance' => DB::raw("balance + {$amount}"),
                    'nonce' => $newNonce,
                    'updated_at' => now(),
                ]);

                if (! $updated) {
                    throw new Exception('Conflict occured during balance update, please try again.');
                }

                $transaction->update([
                    'status' => 'completed',
                ]);

                return $transaction;
            });
        } catch (Exception $e) {
            $transaction->update(['status' => 'failed', 'description' => "Deposit failed: {$e->getMessage()}"]);

            return null;
        }
    }

    /**
     * Securely transfer funds from one wallet to another.
     *
     * @param  Wallet  $senderWallet  Fund source.
     * @param  Wallet  $receiverWallet  Fund destination.
     * @param  float  $amount  The amount of money transfered.
     * @return Transaction | null
     *
     * @throws Exception When there is an update collision.
     */
    public static function transfer(Wallet $senderWallet, Wallet $receiverWallet, float $amount)
    {
        // create a new transaction attempt record.
        $transaction = Transaction::create([
            'type' => 'transfer',
            'status' => 'pending',
        ]);

        // attempt to do the transaction.
        try {
            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Amount must not be less than or equal to 0.']);
            }
            if ($senderWallet->balance <= $amount) {
                throw new UnauthorizedException('Sender has insufficient balance to transfer.', 401);
            }
            if ($senderWallet->status != 'available') {
                throw new UnauthorizedException('Sender wallet is either frozen or suspended.', 401);
            }
            if ($receiverWallet->status != 'available') {
                throw new UnauthorizedException('Receiver wallet is either frozen or suspended.', 401);
            }

            return DB::transaction(function () use ($senderWallet, $receiverWallet, $amount, $transaction) {
                // create new ledger for sender
                Ledger::create([
                    'transaction_id' => $transaction->id,
                    'wallet_id' => $senderWallet->id,
                    'type' => 'credit',
                    'amount' => $amount,
                ]);

                // create new ledger for receiver
                Ledger::create([
                    'transaction_id' => $transaction->id,
                    'wallet_id' => $receiverWallet->id,
                    'type' => 'debit',
                    'amount' => $amount,
                ]);

                // update sender balance
                $senderNewNonce = (string) Str::uuid();
                $senderUpdated = Wallet::find($senderWallet->id)->where('nonce', $senderWallet->nonce)->update([
                    'balance' => DB::raw("balance - {$amount}"),
                    'nonce' => $senderNewNonce,
                    'updated_at' => now(),
                ]);

                if (! $senderUpdated) {
                    throw new Exception('Sender conflict, please try again.', 409);
                }

                $receiverNewNonce = (string) Str::uuid();
                $receiverUpdated = Wallet::find($receiverWallet->id)->where('nonce', $receiverWallet->nonce)->update([
                    'balance' => DB::raw("balance + {$amount}"),
                    'nonce' => $receiverNewNonce,
                    'updated_at' => now(),
                ]);

                if (! $receiverUpdated) {
                    throw new Exception('Receiver conflict, please try again.', 409);
                }

                $transaction->update(['status' => 'completed']);

                return $transaction;
            });
        } catch (Exception $e) {
            $transaction->update([
                'status' => 'failed',
                'description' => "Transfer failure: {$e->getMessage()}",
            ]);

            return null;
        }
    }
}
