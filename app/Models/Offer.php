<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Offer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $guarded = [];

    public const OFFER_OVER = 'Offer is over!';

    protected $casts = [
        'offer_start_time' => 'datetime',
        'offer_finish_time' => 'datetime'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id', 'id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function priceCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'text']);
    }

    public function getLogNameToUse(): string
    {
        return 'Offer';
    }
}
