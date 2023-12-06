<?php

namespace App\Console\Commands;

use App\Enums\KeyStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderTypeEnum;
use App\Models\Key;
use App\Models\Order;
use App\Models\SaleInfo;
use App\Services\GameService;
use App\Services\KeyStatusHistoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MakeRejectAutomatically extends Command
{


    public int $diffHours = 3;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-reject-automatically';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(KeyStatusHistoryService $keyStatusHistoryService)
    {

        Log::channel('create_order_etail1')->info('app:make-reject-automatically kronu çalıştı');

        $date = now()->subHours($this->diffHours)->format('Y-m-d H:i:s');

        Order::query()
            ->with(['orderItems:id,game_id,order_id', 'keys'])
            ->where('status', OrderStatus::RESERVE->value)
            ->where('order_type', OrderTypeEnum::FROM_API)
            ->where('reservation_time', '<', $date)
            ->chunk(100, function (Collection $orders) use ($keyStatusHistoryService) {
                /** @var Order $order */
                foreach ($orders as $order) {
                    $order->update(['status' => OrderStatus::AUTO_REJECT->value]);


                    $order->keys()->update([
                        'status' => KeyStatus::ACTIVE,
                        'order_id' => null
                    ]);


                    foreach ($order->orderItems as $orderItem) {


                        DB::transaction(function () use ($order, $orderItem, $keyStatusHistoryService) {

                            $keyIds = $order->keys->where('game_id', $orderItem->game_id)->pluck('id')->toArray();

                            $keyStatusHistoryService->insertData(gameId: $orderItem->game_id, keyIds: $keyIds, keyStatus: KeyStatus::ACTIVE, orderId: $order->id, desc: 'Reservaston expired time');

                            app(GameService::class)->updateStock($orderItem->game_id);
                        });


                    }
                }
            });
    }
}
