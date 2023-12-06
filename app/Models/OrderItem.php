<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\CurrencySymbol;
use App\Services\PriceService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

//    protected $connection = 'test_server';

//    protected $table = 'order_items1';

    protected $guarded = [];

    protected function unitPriceForFront(): Attribute
    {
        return new Attribute(
            get: function () {
                return PriceService::convertFloatForFront($this->unit_price) . CurrencyEnum::from($this->currency_id)->getSymbol();
            }
        );
    }

    protected function subTotal(): Attribute
    {
        return new Attribute(
            get: fn() => PriceService::convertFloatForFront($this->unit_price * $this->quantity) . CurrencySymbol::EUR->value
        );
    }


    protected $appends = ['unit_price_for_front'];

    public function getUnitPriceForFront()
    {
        return PriceService::convertFloatForFront($this->unit_price);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
