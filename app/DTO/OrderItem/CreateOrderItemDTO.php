<?php

namespace App\DTO\OrderItem;

use App\Enums\OrderStatus;
use App\Enums\OrderTypeEnum;
use App\Http\Requests\Api\Order\CreateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\Support\Arrayable;

class CreateOrderItemDTO implements Arrayable
{

    public function __construct(
        protected Order      $order,
        protected string|int $gameId,
        protected int        $quantity,
        protected float      $unitPrice,
    )
    {

    }

    public static function fromRequest(CreateOrderRequest $request): void
    {


//        return new static(
//            order: ,
//            gameId: OrderService::createOrderCode('cus'),
//            quantity: $request->input('who'),
//            unitPrice: $totalAmount,
//
//        );
    }

    public function toArray(): array
    {
        return [
//            'order_type' => $this->orderType->value,
//            'order_code' => $this->orderCode,
//            'who' => $this->who,
//            'total_amount' => $this->totalAmount,
//            'amount_currency_id' => $this->amountCurrencyId,
//            'status' => $this->status,
//            'customer_id' => $this->customerId
        ];
    }
}
