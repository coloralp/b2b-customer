<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\CurrencySymbol;
use App\Services\PriceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];


    protected $with = ['creator', 'currencyInfo','expenseType'];
    protected $casts = [
        'amount_currency' => CurrencyEnum::class
    ];


    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'who');
    }

    public function currencyInfo(): BelongsTo
    {
        return $this->belongsTo(ExchangeInfo::class, 'currency_info_id');
    }

    protected function amountConvertEurFront(): Attribute
    {
        return Attribute::make(get: fn() => PriceService::convertFloatForFront($this->amount_convert_eur)
            . CurrencySymbol::EUR->value);
    }

    protected function amountFront(): Attribute
    {
        return Attribute::make(get: fn() => PriceService::convertFloatForFront($this->amount)
            . $this->amount_currency->getSymbol());
    }

    public function scopeGetExpenseByDate(Builder $query, $start, $end = null)
    {

        try {
            $start = \Carbon\Carbon::parse($start)->format('Y-m-d H:i:s');
            if (!is_null($end)) {
                $end = Carbon::parse($end)->format('Y-m-d H:i:s');
            }
        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }

        return $query->where('created_at', '>=', $start)
            ->when(!is_null($end), function ($query) use ($end) {
                $query->where('created_at', '<=', $end);
            });

    }

    public function scopeGetExpenseByMonthAndYear(Builder $query, $month, $year = null)
    {

        return PriceService::convertStrToFloat($query->whereYear('created_at', $year)->whereMonth('created_at', $month)->sum('amount_convert_eur'));


    }
}
