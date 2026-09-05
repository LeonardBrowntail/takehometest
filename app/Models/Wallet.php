<?php

namespace App\Models;

use App\Enums\LedgerType;
use App\Enums\WalletStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property int $owner_id
 * @property int $amount
 * @property string $status
 * @property string|null $nonce
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Ledger> $ledgers
 * @property-read int|null $ledgers_count
 * @property-read User|null $owner
 * @property-read Collection<int, Transaction> $transactions
 * @property-read int|null $transactions_count
 *
 * @method static \Database\Factories\WalletFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereNonce($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Wallet extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'amount',
        'status',
        'nonce',
    ];

    protected $casts = [
        'status' => WalletStatus::class,
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Ledger::class, 'wallet_id', 'id', 'id', 'transaction_id');
    }

    // public function getCalculatedBalance(): int
    // {
    //     $debits = $this->ledgers()->where('type', LedgerType::DEBIT->value)->sum('amount');
    //     $credits = $this->ledgers()->where('type', LedgerType::CREDIT->value)->sum('amount');

    //     return (int) ($debits - $credits);
    // }
}
