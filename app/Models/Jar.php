<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Services\PriceService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jar extends Model
{
    use HasFactory, SoftDeletes;

    public const OUR_JAR = 'eser@cdkeyci.com';

    protected $casts = [
        'currency' => CurrencyEnum::class
    ];


    protected $guarded = [];

    protected function balanceFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloatForFront($this->balance) . $this->currency->getSymbol()
        );
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function currencyChanges(): HasMany
    {
        return $this->hasMany(CurrencyChange::class, 'jar_id', 'id');
    }

    public function jarTransactions(): HasMany
    {
        return $this->hasMany(JarTransaction::class, 'jar_id', 'id');
    }

    public function jarTransactionRequests(): HasMany
    {
        return $this->hasMany(JarTransactionsRequest::class, 'jar_id', 'id');
    }
}
