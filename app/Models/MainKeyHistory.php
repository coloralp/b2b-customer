<?php

namespace App\Models;

use App\Enums\KeyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MainKeyHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    //relations
    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class, 'key_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function lastChild(): HasOne
    {
        return $this->hasOne(MainKeyHistory::class, 'parent_id')
            ->latestOfMany()
            ->where('status', KeyStatus::SOLD->value);
    }

    //scopes
    public function scopeGetParents(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
}
