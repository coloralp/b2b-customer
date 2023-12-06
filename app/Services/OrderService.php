<?php

namespace App\Services;

use App\Enums\CurrencyEnum;
use App\Enums\GameEventsEnum;
use App\Enums\KeyStatus;
use App\Enums\MarketplaceName;
use App\Enums\OrderDataEnum;
use App\Enums\OrderStatus;
use App\Enums\OrderTypeEnum;
use App\Http\Requests\Api\Order\ApiOrderListFilterRequest;
use App\Http\Requests\Api\Order\OrderListRequest;
use App\Http\Requests\Api\Order\PanelOrderListFilterRequest;
use App\Http\Resources\Api\Order\MainPageLastFiveOrdersResource;
use App\Jobs\GameEventsJob;
use App\Jobs\Order\SendOrderFile;
use App\Models\ApiSaleLimit;
use App\Models\BasketItem;
use App\Models\Currency;
use App\Models\Key;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SaleInfo;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Self_;
use ZipArchive;

class OrderService
{
    use ApiTrait;

    public static function createOrderCode(string $prefix): string
    {
        $orderCode = uniqid($prefix);
        do {
            $orderCode = uniqid($prefix);
        } while (Order::where('order_code', $orderCode)->exists());

        return $orderCode;
    }

    public static function defineOrderType($orderType): string
    {
        $orderType = (int)$orderType;
        return match ($orderType) {
            OrderTypeEnum::FROM_API->value => 'Api Order',
            OrderTypeEnum::TO_CUSTOMER->value => 'Customer Order',
            default => 'Belirlenemedi'
        };
    }


    public function getLastFiveOrders(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $orders = Order::whereOrderType(OrderTypeEnum::TO_CUSTOMER->value)->orderByDesc('created_at')->limit(10)->whereNotNull('customer_id')
            ->with(['customer:id,name', 'orderItems' => ['game:id,uuid,name']])->withCount(['keys'])->get();

        return MainPageLastFiveOrdersResource::collection($orders);
    }

    public static function defineCustomer($data)
    {
        if (!$data->customer_id and !$data->match_id) {
            return 'Not found';
        }

        return match (true) {
            !is_null($data->customer_id) => $data->customer->name,
            !is_null($data?->match_id) => MarketplaceName::defineCustomer($data->match?->marketplace_id),
            default => 'Not found'
        };
    }

    public function prepareForCreatingOrderFiles(Order $order): array
    {
        try {
            $data = [];

            foreach ($order->orderItems as $orderItem) {

                if ($orderItem->game) {
                    $gameId = $orderItem->game->id;
                    $data[] = [
                        'game_id' => $gameId,
                        'game_name' => $orderItem->game->name,
                        'keys' => $order->keys->where('game_id', $gameId)->pluck('key')->toArray()
                    ];
                }
            }

            $data['order_code'] = $order->order_code;

            return $data;
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function createFiles(array $contents): void
    {
        if (!Storage::exists('orderZip')) {
            Storage::createDirectory('orderZip');
        }
        $ordCode = $contents['order_code'];
        $zipFileName = storage_path('app/orderZip/') . $ordCode . '.zip';
        $password = 'cdkeyci' . $ordCode;

        foreach ($contents as $index => $testContent) {
            if ($index != 'order_code') {
                $gameName = preg_replace('/[^A-Za-z0-9]+/', '-', $testContent['game_name']);
                $fileName = "$gameName-$ordCode" . '.txt';
                $count = count($testContent['keys']);
                $keysAsString = implode("\n", $testContent['keys']);
                $content = "$ordCode\n$gameName-$count units\n---------------------------\n$keysAsString";
                Storage::disk('order_contents')->put($fileName, $content);
            }
        }

        $files = Storage::disk('order_contents')->files();

        $zip = new \ZipArchive;
        $zip->open($zipFileName, ZIPARCHIVE::CREATE);

        $zip->setPassword($password);

        foreach ($files as $file) {

            if (str_contains($file, $ordCode)) {
                $fromNameInZipFile = storage_path('app/order_contents/' . $file);
                $zip->addFile($fromNameInZipFile, $file);
                $zip->setEncryptionName($file, ZipArchive::EM_AES_256);
            }
        }

        $zip->close();

        Storage::disk('order_contents')->delete($files);
    }

    public static function defineCreatedByOrder($data)
    {
        $createdByAsUser = $data->createdBy ?? 'yok';


        if ($createdByAsUser == 'yok' and $data->match) {
            return MarketplaceName::defineApiName($data->match->marketplace_id);
        }

        if ($createdByAsUser != 'yok') {
            return $createdByAsUser->full_name;
        }

        return 'Belirlenmedi';
    }

    public function orderListAll(array $payload)
    {

        $request = new OrderListRequest($payload);


        return Order::when($request->filled('start'), function (Builder $builder) use ($request) {
            $builder->whereDate('created_at', '>=', Carbon::parse($request->input('start')));
        })
            ->when($request->filled('finish'), function (Builder $builder) use ($request) {
                $builder->whereDate('created_at', '<=', Carbon::parse($request->input('finish')));
            })
            ->with('amountCurrency', 'createdBy', 'customer')
            ->withCount('reserveKeys', 'soldKeys')
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $searchTerm = trim($request->input('search'));
                $builder->where('order_code', 'like', "$searchTerm");
            })
            ->when($request->filled('order_type'), function (Builder $builder) use ($request) {
                $builder->where('order_type', $request->input('order_type'));
            })
            ->when($request->filled('order_status'), function (Builder $builder) use ($request) {
                $builder->where('status', $request->input('order_status'));
            })
            ->when($request->filled('customer_id'), function (Builder $builder) use ($request) {

                $builder->where('customer_id', $request->input('customer_id'));
            })
            ->when($request->filled('gameId'), function (Builder $query) use ($request) {
                $query->whereHas('keys', function (Builder $query) use ($request) {
                    $query->where('game_id', $request->input('gameId'));
                });
            })
            //created at
            ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::ASC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::ASC->value);
            })
            ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::DESC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::DESC->value);
            });
    }

    public function panelOrderList(array $payload)
    {

        $request = new PanelOrderListFilterRequest($payload);

        return Order::query()
            ->where('order_type', OrderTypeEnum::FROM_CUSTOMER_PANEL)
            ->when($request->filled('max_amount'), function (Builder $builder) use ($request) {
                $builder->where('total_amount', '<=', $request->input('max_amount'));
            })
            ->when($request->filled('min_amount'), function (Builder $builder) use ($request) {
                $builder->where('total_amount', '>=', $request->input('min_amount'));
            })
            ->when($request->filled('start'), function (Builder $builder) use ($request) {
                $builder->whereDate('created_at', '>=', Carbon::parse($request->input('start')));
            })
            ->when($request->filled('finish'), function (Builder $builder) use ($request) {
                $builder->whereDate('created_at', '<=', Carbon::parse($request->input('finish')));
            })
            ->with('amountCurrency', 'createdBy', 'customer')
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $searchTerm = trim($request->input('search'));
                $builder->where('order_code', 'like', "$searchTerm");
            })
            ->when($request->filled('order_status'), function (Builder $builder) use ($request) {
                $builder->where('status', $request->input('order_status'));
            })
            ->when($request->filled('customer_id'), function (Builder $builder) use ($request) {
                $builder->where('customer_id', $request->input('customer_id'));
            })
            ->when($request->filled('gameId'), function (Builder $query) use ($request) {
                $query->whereHas('keys', function (Builder $query) use ($request) {
                    $query->where('game_id', $request->input('gameId'));
                });
            })
            ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::ASC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::ASC->value);
            })
            ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::DESC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::DESC->value);
            })
            ->when($request->filled('order_by_key_count') and $request->input('order_by_created') == OrderDataEnum::ASC->value, function ($query) {
                $query->orderBy('piece', OrderDataEnum::ASC->value);
            })
            ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::DESC->value, function ($query) {
                $query->orderBy('piece', OrderDataEnum::DESC->value);
            });


    }


    public function orderApiSells(array $payload)
    {

        $request = new ApiOrderListFilterRequest($payload);

        return Order::apiSoldRelations()
            ->when($request->filled('start'), function (Builder $builder) use ($request) {
                $builder->whereDate('created_at', '>=', Carbon::parse($request->input('start')));
            })
            ->when($request->filled('finish'), function (Builder $builder) use ($request) {
                $builder->whereDate('created_at', '<=', Carbon::parse($request->input('finish')));
            })
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $searchTerm = trim($request->input('search'));
                $builder->where('order_code', 'like', "%$searchTerm%");
            })
            ->when($request->filled('order_status'), function (Builder $builder) use ($request) {
                $builder->where('status', $request->input('order_status'));
            })
            ->when($request->filled('gameId'), function (Builder $query) use ($request) {
                $query->whereHas('keys', function (Builder $query) use ($request) {
                    $query->where('game_id', $request->input('gameId'));
                });
            })
            ->when($request->filled('marketplaces'), function ($query) use ($request) {
                $query->withWhereHas('match', function ($query) use ($request) {
                    $query->whereIn('marketplace_id', $request->input('marketplaces'));
                });
            })
            //created at
            ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::ASC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::ASC->value);
            })
            ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::DESC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::DESC->value);
            })
            ->orderBy('created_at', OrderDataEnum::DESC->value)
            ->whereRelation('match', 'marketplace_id', '!=', MarketplaceName::ETAIL->value)
            ->orderBy('created_at', OrderDataEnum::DESC->value);
    }


    public function orderEtailSales(array $payload)
    {
        $request = new ApiOrderListFilterRequest($payload);

        return Order::when($request->filled('start'), function (Builder $builder) use ($request) {
            $builder->whereDate('created_at', '>=', Carbon::parse($request->input('start')));
        })
            ->when($request->filled('finish'), function (Builder $builder) use ($request) {
                $builder->whereDate('created_at', '<=', Carbon::parse($request->input('finish')));
            })->apiSoldRelations()
            ->whereRelation('match', 'marketplace_id', '=', MarketplaceName::ETAIL->value)
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $searchTerm = trim($request->input('search'));
                $builder->where('order_code', 'like', "%$searchTerm%");
            })
            ->when($request->filled('order_status'), function (Builder $builder) use ($request) {
                $builder->where('status', $request->input('order_status'));
            })
            ->when($request->filled('start'), function (Builder $query) use ($request) {
                $query->whereDate('created_at', '>=', Carbon::createFromFormat('d.m.Y H:i:s', $request->input('start')));
            })
            //created at
            ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::ASC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::ASC->value);
            })
            ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::DESC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::DESC->value);
            })
            ->orderBy('created_at', OrderDataEnum::DESC->value);
    }


    public function checkLimit(MarketplaceName $marketplace, $gameId, array $currentData): array
    {
        $canBuy = true;
        $message = 'Satılabilir';


        $hourly = $currentData['hourly'] ?? 0;
        $daily = $currentData['daily'] ?? 0;
        $monthly = $currentData['monthly'] ?? 0;

        $limitWithGame = ApiSaleLimit::getActives()->whereGameId($gameId)->whereMarketplaceId($marketplace->value)->first();


        if ($limitWithGame) {
            $canBuy = !($currentData['daily'] >= $limitWithGame->daily);
            $message = !$canBuy ? "Günlül satış limitiniz {$limitWithGame->daily},aştınız!" : $message;

            return ['isOrderAble' => $canBuy, 'message' => $message];
        }

        $limitWithout = ApiSaleLimit::getActives()->whereMarketplaceId($marketplace->value)->whereNull('game_id');

        if ($limitWithout->exists()) {


            $limit = $limitWithout->first();


            if ($hourly >= $limit->hourly) {
                $message = "Saatlik satış limitiniz {$limit->hourly},aştınız!";
                $canBuy = false;
                return ['isOrderAble' => $canBuy, 'message' => $message];

            }

            if ($monthly >= $limit->monthly) {
                $message = "Aylık satış limitiniz {$limit->monthly},aştınız!";
                $canBuy = false;
                return ['isOrderAble' => $canBuy, 'message' => $message];

            }

            if ($daily >= $limit->daily) {
                $message = "Günlük satış limitiniz {$limit->daily},aştınız!";
                $canBuy = false;
                return ['isOrderAble' => $canBuy, 'message' => $message];
            }


        } else {
            $canBuy = false;
            $message = "Satılabilir";
        }

        return ['isOrderAble' => $canBuy, 'message' => $message];
    }

    public function detail(Request $request): \Illuminate\Database\Eloquent\Model|Order|Builder|\LaravelIdea\Helper\App\Models\_IH_Order_QB
    {
        $perPage = request()->input('per_page') ?? config('general_settings.default_page');
        $currenPage = request()->input('current_page') ?? 1;

        $orderCode = $request->input('order_code');


        return Order::where('order_code', $orderCode)->with(['match', 'createdBy', 'customer:id,name', 'amountCurrency', 'orderItems' => ['currency'],
            'keys' => function ($query) use ($request, $currenPage, $perPage) {
                $query
                    ->when($request->filled('search'), function ($query) use ($request) {
                        $search = trim($request->input('search'));
                        $query->where('key', 'like', "%$search%");
                    })
                    // keyi sillinmiş bile olsa görsünler diye
                    ->withTrashed()
                    ->with(['supplier' => function ($query) {
                        $query->orderBy('name')
                            ->select('id', 'name');
                    }, 'game:id,name,status', 'saleInfo'])
                    ->simplePaginate($perPage, ['*'], 'page', $currenPage);

            }])->firstOrFail();
    }

    public static function createPanelCustomerOrder(string|int $customerId): void
    {

        DB::transaction(function () use ($customerId) {

            $totalPrice = 0;
            $basketItems = BasketItem::whereWho($customerId);

            $basketItems->chunkById(10, function (Collection $collection) use (&$totalPrice) {
                /** @var BasketItem $item */
                foreach ($collection as $item) {
                    $totalPrice += $item->qty * $item->unit_price;
                }
            });

            $order = Order::create([
                'order_type' => OrderTypeEnum::FROM_CUSTOMER_PANEL->value,
                'order_code' => self::createOrderCode('cus'),
                'who' => $customerId,
                'total_amount' => $totalPrice,
                'amount_currency_id' => CurrencyEnum::EUR->value,
                'customer_id' => $customerId,
            ]);

            $basketItems->chunkById(10, function (Collection $collection) use ($order) {
                /** @var BasketItem $item */
                foreach ($collection as $item) {
                    $order->orderItems()->create([
                        'game_id' => $item->game_id,
                        'quantity' => $item->qty,
                        'unit_price' => $item->unit_price,
                        'currency_id' => CurrencyEnum::EUR->value
                    ]);
                }
            });

            $basketItems->delete();
        });
    }


    public function getOrderItems($payload): Builder|\LaravelIdea\Helper\App\Models\_IH_OrderItem_QB
    {
        $request = new OrderListRequest($payload);


        return OrderItem::query()
            ->withWhereHas('order', function ($query) use ($request) {
                $query->when($request->filled('start'), function (Builder $builder) use ($request) {
                    $builder->whereDate('created_at', '>=', Carbon::parse($request->input('start')));
                })
                    ->when($request->filled('finish'), function (Builder $builder) use ($request) {
                        $builder->whereDate('created_at', '<=', Carbon::parse($request->input('finish')));
                    })
                    ->with(['customer', 'firstKey:id,cost,order_id,cost_currency_id,cost_convert_euro'])
                    ->when($request->filled('search'), function (Builder $builder) use ($request) {
                        $searchTerm = trim($request->input('search'));
                        $builder->where('order_code', 'like', "$searchTerm");
                    })
                    ->when($request->filled('order_type'), function (Builder $builder) use ($request) {
                        $builder->where('order_type', $request->input('order_type'));
                    })
                    ->when($request->filled('order_status'), function (Builder $builder) use ($request) {
                        $builder->where('status', $request->input('order_status'));
                    })
                    ->when($request->filled('customer_id'), function (Builder $builder) use ($request) {

                        $builder->where('customer_id', $request->input('customer_id'));
                    })
                    ->when($request->filled('gameId'), function (Builder $query) use ($request) {
                        $query->whereHas('keys', function (Builder $query) use ($request) {
                            $query->where('game_id', $request->input('gameId'));
                        });
                    })
                    //created at
                    ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::ASC->value, function ($query) {
                        $query->orderBy('created_at', OrderDataEnum::ASC->value);
                    })
                    ->when($request->filled('order_by_created') and $request->input('order_by_created') == OrderDataEnum::DESC->value, function ($query) {
                        $query->orderBy('created_at', OrderDataEnum::DESC->value);
                    })->select(['order_code', 'id', 'customer_id', 'created_at', 'status', 'total_amount', 'amount_currency_id']);
            })->with('game');


    }

    public function whenOrderUpdateAsReject(Order $order, KeyStatusHistoryService $keyStatusHistoryService): mixed
    {
        return DB::transaction(function () use ($keyStatusHistoryService, $order) {
            $order->update(['status' => OrderStatus::REJECT->value]);

            foreach ($order->orderItems as $orderItem) {
                $keyStatusHistoryService->insertData(gameId: $orderItem->game_id, keyIds: $order->keys->where('game_id', $orderItem->game_id)->pluck('id')->toArray(), keyStatus: KeyStatus::RESERVED, orderId: $order->id, desc: 'order iptal');
                app(GameService::class)->updateStock($orderItem->game_id);
                dispatch(new GameEventsJob(gameEvent: GameEventsEnum::UPDATED, gameId: $orderItem->game_id, keyIds: [], keysStatus: KeyStatus::ACTIVE->value, fromEtail: false, orderId: $order->id));
            }

            $order->keys()->update(['status' => KeyStatus::ACTIVE->value, 'order_id' => null, 'sale_info_id' => null]);

            return true;
        });
    }

    public function whenPanelOrderApprove(Order $order, KeyStatusHistoryService $keyStatusHistoryService): mixed
    {
        return DB::transaction(function () use ($keyStatusHistoryService, $order) {
            $keys = collect([]);

            $myCurrency = Currency::find($order->amount_currency_id);

            $order->update(['status' => OrderStatus::APPROVE]);

            foreach ($order->orderItems as $item) {

                $saleInfo = SaleInfo::create([
                    'amount_currency_id' => $item->currency_id,
                    'amount' => $item->unit_price,
                    'amount_convert_euro' => CurrencyService::convertEur($item->unit_price, $myCurrency),
                ]);

                $stockData = GameService::getOnlyStock($item->game_id);

                if ($item->quantity > $stockData) {
                    $message = "{$item->game->name} için yeteri kadar stock yok({$item->game_id}) Stok: $stockData. İstenilen : {$item->quantity}";
                    return $this->returnWithMessage($message);
                }

                $reserveKeys = Key::whereGameId($item->game_id)->whereStatus(KeyStatus::ACTIVE->value)->limit($item->quantity)->lockForUpdate();

                $keys = $keys->merge($reserveKeys->get());

                $reserveKeys->update([
                    'order_id' => $order->id,
                    'status' => KeyStatus::SOLD->value,
                    'sale_info_id' => $saleInfo->id
                ]);

                $keyStatusHistoryService->insertData(gameId: $item->game_id, keyIds: $reserveKeys->pluck('id')->toArray(), keyStatus: KeyStatus::RESERVED, orderId: $order->id, desc: 'Paneldeki customer onay reserve');
                $keyStatusHistoryService->insertData(gameId: $item->game_id, keyIds: $reserveKeys->pluck('id')->toArray(), keyStatus: KeyStatus::SOLD, orderId: $order->id, desc: 'Paneldeki customer onay sold');
            }

            dispatch(new SendOrderFile($order->order_code));

            return true;
        });
    }
}
