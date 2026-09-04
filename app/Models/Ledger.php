<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\LedgerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $transaction_id
 * @property string $wallet_id
 * @property numeric $amount
 * @property string $type
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read \App\Models\Transaction $transaction
 * @property-read \App\Models\Wallet|null $wallet
 * @method static \Database\Factories\LedgerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ledger whereWalletId($value)
 * @mixin \Eloquent
 */
class Ledger extends Model
{
    /** @use HasFactory<LedgerFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
