<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Services\PriceService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyChange extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'currency_changes';

    protected $casts = [
        'old_currency' => CurrencyEnum::class,
        'new_currency' => CurrencyEnum::class,
    ];

    protected function oldBalanceFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloat($this->old_balance) . $this->old_currency->getSymbol()
        );
    }

    protected function newBalanceFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloat($this->new_balance) . $this->new_currency->getSymbol()
        );
    }

    public function changeBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'change_by', 'id');
    }

    public function oldCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'old_currency', 'id');
    }

    public function newCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'new_currency', 'id');
    }
}
