<?php

namespace App\Models;

use App\Enums\CurrencySymbol;
use App\Services\PriceService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class Summary extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const MONTHS = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];


    public static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            $month = Str::length($data['month_id']) == 1 ? "0{$data['month_id']}" : $data['month_id'];
            $stringAs = "01-$month-{$data['year_id']}";
            $data['date'] = Carbon::parse($stringAs)->startOfMonth();

        });
    }

    //todo bunları laravel data ile yapmayı öğren

    protected function totalCostFront(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->frontData($this->total_cost)
        );
    }

    protected function addExpenseFront(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->frontData($this->add_cost)
        );
    }

    protected function totalMarginFront(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->frontData($this->total_margin)
        );
    }

    protected function totalGiroFront(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->frontData($this->total_giro)
        );
    }


    protected function netCostFront(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->frontData($this->net_cost)
        );
    }

    protected function netMarginFront(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->frontData($this->net_margin)
        );
    }

    #[ArrayShape(['text' => "int|string", 'data' => "float|int"])] protected function frontData($value): array
    {
        return [
            'text' => PriceService::convertFloatForFront($value, true),
            'data' => PriceService::convertFloat($value)
        ];
    }
}
