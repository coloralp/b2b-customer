<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function transaction(): HasManyThrough
    {
        return $this->hasManyThrough(
            BankTransaction::class,
            BankAccount::class,
            'bank_id',
            'bank_account_id'
        );
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'bank_id');
    }
}
