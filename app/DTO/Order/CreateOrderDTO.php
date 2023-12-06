<?php

namespace App\DTO\Order;

use App\Enums\OrderStatus;
use App\Enums\OrderTypeEnum;
use App\Http\Requests\Api\BankAccount\CreateBankAccountRequest;
use App\Http\Requests\Api\BankAccount\UpdateBankAccountRequest;
use App\Http\Requests\Api\Order\CreateOrderRequest;
use App\Services\OrderService;
use App\Services\PriceService;
use Illuminate\Contracts\Support\Arrayable;

class CreateOrderDTO implements Arrayable
{

    public function __construct(
        protected OrderTypeEnum $orderType,
        protected string        $orderCode,
        protected int|string    $who,
        protected float         $totalAmount,
        protected int           $piece,
        protected int|string    $amountCurrencyId,
        protected               $status,
        protected string|int    $customerId
    )
    {

    }

    public static function fromRequest(CreateOrderRequest $request): CreateOrderDTO
    {

        $customerId = $request->input('customer_id');
        $currency = $request->input('currency');
        $totalAmount = $request->input('totalAmount');

        if (is_string($totalAmount)) {
            $totalAmount = PriceService::convertStrToFloat($totalAmount);
        }

        return new static(
            orderType: $request->filled('order_type') ? OrderTypeEnum::from($request->input('order_type')) : OrderTypeEnum::TO_CUSTOMER,
            orderCode: OrderService::createOrderCode('cus'),
            who: $request->input('who'),
            totalAmount: $totalAmount,
            piece: $request->input('totalQuantity'),
            amountCurrencyId: $currency,
            status: $request->filled('status') ? OrderStatus::from($request->input('status'))->value : OrderStatus::RESERVE->value,
            customerId: $customerId
        );
    }

    public function toArray(): array
    {
        return [
            'order_type' => $this->orderType->value,
            'order_code' => $this->orderCode,
            'who' => $this->who,
            'total_amount' => $this->totalAmount,
            'piece' => $this->piece,
            'amount_currency_id' => $this->amountCurrencyId,
            'status' => $this->status,
            'customer_id' => $this->customerId
        ];
    }
}
