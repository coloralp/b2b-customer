<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class CustomNotification extends Model
{
    use HasFactory, Prunable;

    protected $guarded = [];

    protected $table = 'notifications';

    public function prunable()
    {
        // Files matching this query will be pruned
        return static::query()->where('created_at', '<=', now()->subDays(14));
    }


}
