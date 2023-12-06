<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\CurrencySymbol;
use App\Enums\JarTransactionEnum;
use App\Services\JarTransactionService;
use App\Services\PriceService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JarTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $with = [
        'jar',
        'who',
        'order',
        'deletedBy',
        'game'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (JarTransaction $jarTransaction) {
            $jarTransaction->code = JarTransactionService::createTransactionCode();
        });
    }


    protected $casts = [
        'amount_currency' => CurrencyEnum::class,
        'jar_transaction_type' => JarTransactionEnum::class
    ];

    protected function processAmountFront(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMathSymbol()
                . PriceService::convertFloat($this->amount)
                . $this->amount_currency->getSymbol()
        );
    }

    protected function orderCodeFilament(): Attribute
    {
        return Attribute::make(fn() => $this->order ? $this->order->order_code : '-');
    }

    protected function processSymbol(): Attribute
    {
        return Attribute::make(fn() => $this->getMathSymbol());
    }

    protected function processEurFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloat($this->amount_convert_eur) . CurrencySymbol::EUR->value,
        );
    }

    public function getMathSymbol(): string
    {

        return $this->jar_transaction_type == JarTransactionEnum::EXPENSE ? '-' : '+';
    }

    protected function processCurrentJarFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloat($this->amount_convert_jar)
                . $this->jar->currency->getSymbol(),
        );
    }

    protected function oldBalanceFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloat($this->old_balance)
                . $this->jar->currency->getSymbol(),
        );
    }

    protected function newBalanceFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloat($this->new_balance)
                . $this->jar->currency->getSymbol(),
        );
    }



    //relations

    //for supplier and customer

    public function keys(): HasMany
    {
        return $this->hasMany(Key::class, 'jar_transaction_id');
    }

    public function jar(): BelongsTo
    {
        return $this->belongsTo(Jar::class, 'jar_id', 'id');
    }

    public function who(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }


}
