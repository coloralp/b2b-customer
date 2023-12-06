<?php

namespace App\Models;

use App\Enums\MarketplaceName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceCommission extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(MarketPlace::class, 'marketplace_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
