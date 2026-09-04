<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property int $owner_id
 * @property numeric $balance
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereBalance($value)
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
    /** @use HasFactory<WalletFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'balance',
        'status',
        'nonce',
    ];

    protected $casts = [
        'balance' => 'decimal:4',
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
        return $this->hasManyThrough(Transaction::class, Ledger::class);
    }

    public function getCalculatedBalance(): float
    {
        $debits = $this->ledgers()->where('type', 'debit')->sum('amount');
        $credits = $this->ledgers()->where('type', 'credit')->sum('amount');

        return (float) ($debits - $credits);
    }
}
