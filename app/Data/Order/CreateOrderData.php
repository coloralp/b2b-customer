<?php

namespace App\Data\Order;

use App\Enums\OrderTypeEnum;
use App\Http\Requests\Api\Order\CreateOrderRequest;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class CreateOrderData extends Data
{
    public function __construct(
        public            $orderType,
        public string     $orderCode,
        public int|string $who,
        public float      $totalAmount,
        public int|string $amountCurrencyId,
        public            $status,
        public string|int $customerId
    )
    {

    }

}
