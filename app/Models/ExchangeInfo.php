<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected function dataFront(): Attribute
    {
        return Attribute::make(get: fn() => json_decode($this->data));
    }


}
