<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyStatusTable extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = "key_status";
}
