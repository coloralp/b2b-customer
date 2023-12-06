<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealMatch extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function scopeGetDataWithProductId(Builder $query, $productId, $marketPlaceId): Builder
    {
        return $query->where('product_api_id', $productId)->where('marketplace_id', $marketPlaceId);
    }
}
