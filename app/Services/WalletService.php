<?php

namespace App\Services;

use App\Enums\LedgerType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletStatus;
use App\Exceptions\InsufficientBalanceException;
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
     * @param  int  $amount  Major amount of funds to add (ex: 100).
     * @param  int  $cents  Cent amount of funds to add (ex: 50).
     */
    public static function deposit(Wallet $wallet, int $amount, int $cents): ?Transaction
    {
        WalletService::AssertAmountPositive($amount, $cents);
        WalletService::AssertValidWallet($wallet);

        // create new deposit transaction attempt entry.
        $transaction = WalletService::RecordTransaction(TransactionType::DEPOSIT);

        try {
            return DB::transaction(function () use ($wallet, $amount, $cents, $transaction) {
                // create new ledger.
                WalletService::CreateLedger($transaction, $wallet, LedgerType::DEBIT, $amount, $cents);

                WalletService::updateWallet($wallet, $amount, $cents, LedgerType::DEBIT);

                WalletService::CompleteTransaction($transaction);

                return $transaction;
            });
        } catch (Exception $e) {
            WalletService::FailTransaction($transaction, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Securely transfer funds from one wallet to another.
     *
     * @param  Wallet  $senderWallet  Fund source.
     * @param  Wallet  $receiverWallet  Fund destination.
     * @param  int  $amount  Major amount of funds to transfer (ex: 100).
     * @param  int  $cents  Cent amount of funds to transfer (ex: 50).
     *
     * @throws Exception When there is an update collision.
     */
    public static function transfer(Wallet $senderWallet, Wallet $receiverWallet, int $amount, int $cents): ?Transaction
    {
        WalletService::AssertValidWallet($senderWallet);
        WalletService::AssertValidWallet($receiverWallet);
        WalletService::AssertAmountPositive($amount, $cents);

        $transaction = WalletService::RecordTransaction(TransactionType::TRANSFER);

        // attempt to do the transaction.
        try {
            return DB::transaction(function () use ($senderWallet, $receiverWallet, $amount, $cents, $transaction) {
                WalletService::AssertWalletSufficient($senderWallet, $amount, $cents);

                WalletService::CreateLedger($transaction, $senderWallet, LedgerType::CREDIT, $amount, $cents);

                WalletService::CreateLedger($transaction, $receiverWallet, LedgerType::DEBIT, $amount, $cents);

                WalletService::updateWallet($senderWallet, $amount, $cents, LedgerType::CREDIT);

                WalletService::updateWallet($receiverWallet, $amount, $cents, LedgerType::DEBIT);

                WalletService::CompleteTransaction($transaction);

                return $transaction;
            });
        } catch (Exception $e) {
            WalletService::FailTransaction($transaction, $e->getMessage());
            throw $e;
        }
    }

    private static function RecordTransaction(TransactionType $type)
    {
        return Transaction::create([
            'type' => $type,
            'status' => TransactionStatus::PENDING,
            'description' => 'Pending transaction',
        ]);
    }

    private static function CompleteTransaction(Transaction $transaction)
    {
        $transaction->update(['status' => TransactionStatus::COMPLETED, 'description' => 'Transaction completed.']);
    }

    private static function FailTransaction(Transaction $transaction, string $reason)
    {
        $transaction->update([
            'status' => TransactionStatus::FAILED,
            'description' => "Failed transaction: {$reason}",
        ]);
    }

    private static function CreateLedger(Transaction $transaction, Wallet $wallet, LedgerType $type, int $amount, int $cents)
    {
        Ledger::create([
            'transaction_id' => $transaction->id,
            'wallet_id' => $wallet->id,
            'type' => $type,
            'amount' => MoneyService::totalCents($amount, $cents),
        ]);
    }

    private static function updateWallet(Wallet $wallet, int $amount, int $cents, LedgerType $type)
    {
        $newNonce = (string) Str::uuid();
        $value = MoneyService::totalCents($amount, $cents);
        if ($type === LedgerType::DEBIT) {
            $updated = Wallet::where('id', $wallet->id)->where('nonce', $wallet->nonce)->increment('amount', $value, ['nonce' => $newNonce]);
        } else {
            $updated = Wallet::where('id', $wallet->id)->where('nonce', $wallet->nonce)->decrement('amount', $value, ['nonce' => $newNonce]);
        }
        if (! $updated) {
            throw new Exception('Conflict occured during balance update, please try again.');
        }
    }

    private static function AssertAmountPositive(int $amount, int $cents)
    {
        if (MoneyService::totalCents($amount, $cents) <= 0) {
            throw ValidationException::withMessages(['amount' => 'Amount cannot be less than or equal to 0.']);
        }
    }

    private static function AssertValidWallet(Wallet $wallet)
    {
        if (! $wallet || ! ($wallet instanceof Wallet)) {
            throw new UnauthorizedException('Wallet instance not found.');
        }
        if ($wallet->status !== WalletStatus::ACTIVE) {
            throw new UnauthorizedException("Wallet ({$wallet->id}) is either frozen or suspended.");
        }
    }

    private static function AssertWalletSufficient(Wallet $wallet, int $amount, int $cents)
    {
        if ($wallet->amount < MoneyService::totalCents($amount, $cents)) {
            throw new InsufficientBalanceException('Insufficient balance.');
        }
    }
}
