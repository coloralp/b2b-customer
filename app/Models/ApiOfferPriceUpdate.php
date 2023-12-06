<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiOfferPriceUpdate extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function match(): BelongsTo
    {
        return $this->belongsTo(MarketplaceMatchGame::class, 'match_id','id');
    }

    public function update_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'who');
    }
}
