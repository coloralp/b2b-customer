<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasketItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
//        static::creating(function (BasketItem $basketItem) {
//            $basketItem->who = auth()->id();
//            $basketItem->unit_price = $basketItem->game->amount;
//        });
    }

    public function owner(): BelongsTo//customer
    {
        return $this->belongsTo(User::class, 'who');
    }

    public function game(): BelongsTo//customer
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
