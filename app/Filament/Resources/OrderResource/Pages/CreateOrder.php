<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\DTO\Order\CreateOrderDTO;
use App\Enums\CurrencyEnum;
use App\Enums\OrderStatus;
use App\Enums\OrderTypeEnum;
use App\Filament\Resources\OrderResource;
use App\Http\Requests\Api\Order\CreateOrderRequest;
use App\Models\BasketItem;
use App\Models\Order;
use App\Services\PriceService;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function beforeCreate(): void
    {

        //check balance
        $currentBalance = auth()->user()->jar->balance;
        $willApproveOrder = PriceService::convertFloat(Order::getWillApprove(auth()->id())->sum('total_amount'));



    }

    protected function handleRecordCreation(array $data): Model
    {

        $record = (new ($this->getModel())($data));

        if (
            static::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $orderItems = $record->orderItems ?? [];


        $games = [];
        $total = 0;


        foreach ($orderItems as $item) {
            $qty = $item['quantity'];
            $unit = $item['unit_price'];
            $games[] = ['game_id' => $item['game_id'], 'sales_price' => $unit, 'quantity' => $qty];
            $total += ($qty * $unit);
        }


        //check balance
        $currentBalance = auth()->user()->jar->balance;


        $willApproveOrder = PriceService::convertFloat(Order::getWillApprove(auth()->id())->sum('total_amount'));


        $willSubBalance = $willApproveOrder + $total;


        if ($willSubBalance > $currentBalance) {
            $message = "You have €$willApproveOrder + this order($total) = $willSubBalance But your balance is: $currentBalance }";
            \Filament\Notifications\Notification::make()
                ->title('Balance Error')
                ->body($message)
                ->duration(7000)
                ->danger()
                ->send();

            $record['id'] = 10000000000000000;

            return $record;

        }


        $createOrderRequestData = [
            'games' => $games,
            'customer_id' => auth()->id(),
            'currency' => CurrencyEnum::EUR->value,
            'totalAmount' => $total,
            'who' => auth()->id(),
            'order_type' => OrderTypeEnum::FROM_CUSTOMER_PANEL->value,
            'status' => OrderStatus::CREATED->value,
        ];


        $request = new CreateOrderRequest($createOrderRequestData);


        $createOrderDto = CreateOrderDTO::fromRequest($request);

        $orderId = DB::transaction(function () use ($orderItems, $createOrderDto) {
            $order = Order::create($createOrderDto->toArray());

            foreach ($orderItems as $orderItem) {
                $order->orderItems()->create([
                    'game_id' => $orderItem['game_id'],
                    'quantity' => $orderItem['quantity'],
                    'unit_price' => $orderItem['unit_price']
                ]);
            }

            return $order->id;
        });


        if (!$orderId) {
            \Filament\Notifications\Notification::make()->title(__('orders.went_wrong'))->danger()->send();
        }

        $record['id'] = $orderId;

        //userları belirle gönder

        return $record;

    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }


}
