<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\CurrencySymbol;
use App\Enums\JarTransactionEnum;
use App\Enums\JarTransactionRequestEnum;
use App\Services\JarTransactionService;
use App\Services\PriceService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JarTransactionsRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $with = [
        'jar',
        'updatedBy'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($jarTransaction) {
            $jarTransaction->code = JarTransactionService::createTransactionCode();
        });
    }

    protected $table = 'jar_transaction_requests';

    protected $casts = [
        'amount_currency' => CurrencyEnum::class,
        'jar_transaction_type' => JarTransactionEnum::class,
        'status' => JarTransactionRequestEnum::class
    ];

    public function jar(): BelongsTo
    {
        return $this->belongsTo(Jar::class, 'jar_id', 'id');
    }

    public function who(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }


    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }


    protected function amountJarFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloatForFront($this->will_add_amount) . $this->jar->currency->getSymbol()
        );
    }

    protected function amountFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloatForFront($this->amount) . $this->amount_currency->getSymbol()
        );
    }

    protected function amountEurFront(): Attribute
    {
        return Attribute::make(
            get: fn() => PriceService::convertFloatForFront($this->amount_convert_eur) . CurrencySymbol::EUR->value
        );
    }
}
