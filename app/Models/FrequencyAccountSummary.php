<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrequencyAccountSummary extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'frequency_sales_summaries';

    public static function getEmptyData(): bool|string
    {
        return json_encode(['cost' => 0, 'giro' => 0, 'profit' => 0]);
    }
}
