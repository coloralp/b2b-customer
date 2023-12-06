<?php

namespace App\Models;

use App\Enums\MarketplaceName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiSaleLimit extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'api_sale_limits';

    protected $with = ['marketplace', 'who', 'game'];

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(MarketPlace::class, 'marketplace_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function who(): BelongsTo
    {
        return $this->belongsTo(User::class, 'who');
    }

    public function scopeGetActives(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeGetLimits(Builder $query, MarketplaceName $marketplace, $gameId): Builder
    {
        return $query->where('marketplace_id', $marketplace->value)->where('game_id', $gameId);
    }
}
