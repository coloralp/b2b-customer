<?php

namespace App\Models;

use App\Enums\BankAccountTransactionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'transaction_type' => BankAccountTransactionEnum::class
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function userBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_by');
    }

    public function addBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }


}
