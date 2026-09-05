<?php

namespace App\Models;

use App\Enums\LedgerType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $transaction_id
 * @property string $wallet_id
 * @property numeric $amount
 * @property string $type
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Transaction $transaction
 * @property-read Wallet|null $wallet
 *
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
 *
 * @mixin \Eloquent
 */
class Ledger extends Model
{
    use HasUuids;

    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'type' => LedgerType::class,
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
