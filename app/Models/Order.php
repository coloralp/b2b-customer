<?php

namespace App\Models;

use App\Enums\KeyStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderTypeEnum;
use App\Http\Resources\Api\Order\OrderApiSellListResource;
use App\Http\Resources\Api\Order\OrderListResource;
use App\Services\PriceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'reservation_time' => 'datetime' //string olarak geliyor carbona donusturuldu.
        , 'order_type' => OrderTypeEnum::class,
        'status' => OrderStatus::class,
    ];

    public const ETAIL_RESERVATION_ID = -1;

    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format((float)$value, 2),
            set: function ($value) {
                $value = is_string($value) ? PriceService::convertStrToFloat($value) : $value;
                return str_replace(',', '', number_format($value, 2));
            },
        );
    }


    public function getAmountAsStringAttribute()
    {
        return $this->total_amount . $this->amountCurrency?->symbol;
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MarketplaceMatchGame::class, 'match_id');
    }

    public function keys(): HasMany
    {
        return $this->hasMany(Key::class, 'order_id', 'id');
    }

    public function firstKey(): HasOne
    {
        return $this->keys()->one();
    }

    public function reserveKeys(): HasMany
    {
        return $this->keys()->where('status', KeyStatus::RESERVED->value);
    }

//    public function refundedKeys(): HasMany
//    {
////         eğer bunu öğrenmek istenilirse yani customere ait hangi bilgiler refunded olmuş bunun için
//        //refunded keys diye bir tablo açılır oraya kayıt edilir ama şauan istenmiyor
////        return $this->keys()->where('status', KeyStatus::Re->value);
//    }

    public function soldKeys(): HasMany
    {
        return $this->keys()->where('status', KeyStatus::SOLD->value);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'order_items', 'order_id');
    }

    public function amountCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'amount_currency_id', 'id');
    }

    public function costCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'cost_currency_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'who', 'id');
    }


    // customer is a user for role
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'text']);
    }

    public function getLogNameToUse(): string
    {
        return 'Order';
    }

    public static function boot()
    {
        parent::boot();
        //for order
        //  static::creating(function ($data) {

        // $data['total_amount'] = (float)$data['text'];
        // });
    }

    public function scopeApiSoldRelations(Builder $builder): Builder
    {
        return $builder->where('order_type', OrderTypeEnum::FROM_API->value)->with([
            'createdBy',
            'customer',
            'match' => ['game:id,name,uuid'],
            'keys' => ['saleInfo']
        ])
            ->withCount('reserveKeys', 'soldKeys');
    }

    public function scopeGetCustomerPanelOrder(Builder $query): Builder
    {
        return $query->where('order_type', OrderTypeEnum::FROM_CUSTOMER_PANEL->value);
    }

    public function scopeGetWillApprove(Builder $query, int|string $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }
}
