<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleInfo extends Model
{
    use HasFactory, SoftDeletes, SoftDeletes;

    protected $guarded = [];


//    protected $table = 'sale_infos1';


    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn(float $value) => number_format($value, 2),
        );
    }

    protected function amountConvertEuro(): Attribute
    {
        return Attribute::make(
            //get: fn(float $value) => number_format($value, 2),
            //set: fn(float $value) => number_format($value, 2),
        );
    }

    public function keys(): HasMany
    {
        return $this->hasMany(Key::class, 'sale_info_id');
    }

    public function currencyInfo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'amount_currency_id');
    }

}
