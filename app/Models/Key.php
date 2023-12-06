<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\CurrencySymbol;
use App\Enums\KeyStatus;
use App\Traits\AccountSummaryTrait;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Key extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, SoftDeletes, AccountSummaryTrait, ApiTrait;

    const PRE_DEL = 'DEL-';

    protected $guarded = [];

    //protected $connection = 'b2b_prod';

    public const NOT_SELL = 'Satılmamış';

    protected $casts = [
        'sell_date' => 'datetime'
    ];
    protected $appends = [
        'sale_price',
        'cost_convert_eur_front'
    ];


    //relations


    //for supplier and customer


    public function jarTransaction(): BelongsTo
    {
        return $this->belongsTo(JarTransaction::class, 'jar_transaction_id');
    }

    //take keys in same time

    //bu supplier için key eklemelerde aynı anda aynı transaction içine kiren keyleri kendisi ile beraber veren ilişki
    public function buysInSameTime(): HasMany
    {
        return $this->hasMany(Key::class, 'jar_transaction_id', 'jar_transaction_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(MainKeyHistory::class, 'key_id')->orderBy('created_at');
    }

    public function mainHistories(): HasMany
    {
        return $this->hasMany(MainKeyHistory::class, 'key_id')->orderBy('created_at');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function costCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'cost_currency_id');
    }


    public function saleInfo(): BelongsTo
    {
        return $this->belongsTo(SaleInfo::class, 'sale_info_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id', 'id');
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'who');
    }

    //scopes

    public function scopeGetActives(Builder $query): Builder
    {
        return $query->where('status', KeyStatus::ACTIVE->value);
    }

    public function scopeGetSold(Builder $query): Builder
    {
        return $query->where('status', KeyStatus::SOLD->value);
    }

    public function scopeGetReserves(Builder $query): Builder
    {
        return $query->where('status', KeyStatus::RESERVED->value);
    }

    public function scopeGetAccountSummary(Builder $builder, $month = null, $year = null): Builder
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        return $builder->whereYear('sell_date', $year)
            ->whereMonth('sell_date', $month)
            ->where('status', KeyStatus::SOLD->value);
    }

    public function scopeFilterByDate(Builder $builder, $start, $end = null, $gameId = null): Builder
    {

        return $builder->where('status', KeyStatus::SOLD->value)
            ->when(!is_null($gameId), function (Builder $builder) use ($gameId) {
                $builder->where('game_id', $gameId);
            })
            ->when(is_null($end), function (Builder $builder) use ($start) {
                $builder->whereDate('sell_date', $start);
            }, function (Builder $builder) use ($end, $start) {
                $builder->whereBetween('sell_date', [$start, $end]);
            })
            ->join('sale_infos', 'sale_infos.id', 'keys.sale_info_id')
            ->select(
                DB::raw('SUM(keys.cost_convert_euro) as cost'),
                DB::raw('SUM(sale_infos.amount_convert_euro) as giro'),
                DB::raw('SUM(sale_infos.amount_convert_euro - keys.cost_convert_euro) as profit')
            );

    }

    public function scopeGeneralSummary(Builder $builder, $gameId = null): Builder
    {
        return $builder->where('status', KeyStatus::SOLD->value)
            ->when(!is_null($gameId), function (Builder $builder) use ($gameId) {
                $builder->where('game_id', $gameId);
            })
            ->join('sale_infos', 'sale_infos.id', 'keys.sale_info_id')
            ->select(
                DB::raw('SUM(keys.cost_convert_euro) as cost'),
                DB::raw('SUM(sale_infos.amount_convert_euro) as giro'),
                DB::raw('SUM(sale_infos.amount_convert_euro - keys.cost_convert_euro) as profit')
            );
    }

    public function scopeGetMainPageSummary(Builder $query, Carbon $start, ?Carbon $end = null): Builder
    {
        return $query->with('game:id,name')
            ->where('status', KeyStatus::SOLD->value)
            ->when(!is_null($end), function (Builder $query1) use ($start, $end) {
                $query1->whereBetween('sell_date', [$start, $end]);

            },
                function (Builder $query2) use ($start) {
                    $query2->whereDate('sell_date', $start);
                })
            ->limit(20)
            ->groupBy('game_id')
            ->orderByDesc('total_keys_sold')
            ->having('total_keys_sold', '>=', 1)
            ->select('game_id', DB::raw('count(*) as total_keys_sold'));

    }

    public function scopeGetSoldByDates(Builder $query, $start, $end = null)
    {

        try {
            $start = \Carbon\Carbon::parse($start)->format('Y-m-d H:i:s');
            if (!is_null($end)) {
                $end = Carbon::parse($end)->format('Y-m-d H:i:s');
            }
        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }

        return $query->where('sell_date', '>=', $start)
            ->when(!is_null($end), function ($query) use ($end) {
                $query->where('sell_date', '<=', $end);
            });

    }

    //get attributes and appends

    public function getSellDate()
    {
        return $this->order ?
            $this->sell_date?->format('d.m.Y H:i:s') :
            'Satılamamış';
    }

    public function getSalePriceAttribute(): string
    {
        if (!$this->saleInfo) {
            return '-';
        }
        return ($this->saleInfo?->amount) . ($this->saleInfo?->currencyInfo?->symbol);
    }

    public function getActivitylogOptions(): LogOptions
    {
        // return LogOptions::defaults()->logOnly(collect(Schema::getColumns('keys'))->pluck('name')->toArray()); //herhangi bir değer değişince loglar
        return LogOptions::defaults()->logOnly(['status', 'key', 'supplier', 'game_id', 'deleted_at']);
        // Chain fluent methods for configuration options
    }

    protected function cost(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format((float)$value, 2),
        //set: fn($value) => number_format((float)$value, 2),
        );
    }

    protected function costForFront(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->cost . CurrencyEnum::from($this->cost_currency_id)->getSymbol(),
        );
    }

    protected function costConvertEurFront(): Attribute
    {

        return Attribute::make(
            get: fn() => $this->cost_convert_euro . CurrencySymbol::EUR->value,
        );
    }

    protected function costConvertEuro(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format(floatval($value), 2),
            set: fn($value) => number_format(floatval($value), 2),
        );
    }

    public function getLogNameToUse(): string
    {
        return 'Key';
    }

}
