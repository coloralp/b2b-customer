<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketPlace extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'callback_urls' => 'array'
    ];

    public function saleLimits(): HasMany
    {
        return $this->hasMany(ApiSaleLimit::class, 'marketplace_id')->whereIsActive(1);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'marketplace_match_games', 'marketplace_id', 'game_id', 'id', 'id');
    }

    public function gameActives(): BelongsToMany
    {
        return $this->games()->where('marketplace_match_games.status', 1);
    }

    public function gamePassives(): BelongsToMany
    {
        return $this->games()->where('marketplace_match_games.status', 0);
    }

    public function rezervationss()
    {
        //todo rezarvasyonları çeken
    }

    public function matchesWithSystem()
    {
        //todo bu kinguinle bizim sistemdeki hangi oyun eşleştirilmiş bunu söyleyecek
    }

    public function scopeFindByName(Builder $query, $name)
    {
        return $this->where('name', $name);
    }

    public function commissionSettings(): HasMany
    {
        return $this->hasMany(MarketplaceCommission::class, 'marketplace_id');
    }

    //customer is a user;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
}
