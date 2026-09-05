<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property string $status
 * @property string|null $description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Ledger> $ledgers
 * @property-read int|null $ledgers_count
 *
 * @method static \Database\Factories\TransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'status',
        'description',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
    ];

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }
}
