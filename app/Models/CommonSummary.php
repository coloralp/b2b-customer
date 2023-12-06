<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonSummary extends Model
{
    const MAIN_PAGE_SUMMARY = 'main_page_summary';

    protected $guarded = [];

    use HasFactory;
}
