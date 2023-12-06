<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Services\PriceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $with = [
        'bank'
    ];

    public function getAttributeBalance()
    {
        return PriceService::convertFloat($this->balance) . CurrencyEnum::defineSymbol($this->currency);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
