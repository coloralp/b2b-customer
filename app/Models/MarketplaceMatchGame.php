<?php

namespace App\Models;

use App\Enums\KeyStatus;
use App\Enums\OfferStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceMatchGame extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }

    public function matchGameKeys(): HasMany
    {
        return $this->hasMany(Key::class, 'game_id', 'game_id');
    }


    public function matchActiveKeys(): HasMany
    {
        return $this->matchGameKeys()->where('status', KeyStatus::ACTIVE->value);
    }


    public function matchReserveKeys(): HasMany
    {
        return $this->matchGameKeys()->where('status', KeyStatus::RESERVED->value);
    }

    public function matchSoldKeys(): HasMany
    {
        return $this->matchGameKeys()->where('status', KeyStatus::SOLD->value);
    }

    public function marketPlace(): BelongsTo
    {
        return $this->belongsTo(MarketPlace::class, 'marketplace_id', 'id');
    }

    public function myUpdaters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'api_offer_price_updates', 'match_id', 'who');
    }

    public function updatesData(): HasMany
    {
        return $this->hasMany(ApiOfferPriceUpdate::class, 'match_id');
    }

    //scopes
    public function scopeGetMatch(Builder $builder, $gameId, $offerIdInApi, $marketPlaceId): Builder
    {
        return $builder->where('game_id', $gameId)->where('offer_id', $offerIdInApi, $marketPlaceId);
    }

    public function scopeGetMatchWithOfferId(Builder $builder, $offerIdInApi, $marketPlaceId, $productIdInApi = null): Builder
    {
        return $builder
            ->when(!is_null($productIdInApi), function ($query) use ($productIdInApi) {
                $query->where('product_id', $productIdInApi);
            })
            ->where('status', OfferStatus::ACTIVE->value)
            ->where('offer_id', $offerIdInApi, $marketPlaceId);
    }

    public function scopeGetActive(Builder $query): void
    {
        $query->where('status', 1);
    }
}
