<?php

namespace App\Services;

use App\Enums\KeyStatus;
use App\Enums\MarketplaceName;
use App\Enums\MarketplacePrefix;
use App\Enums\OrderDataEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\OrderUpdateStatus;
use App\Enums\SalesPeriod;
use App\Http\Requests\Api\JarTransaction\UpdateKeyWithTransactionRequest;
use App\Http\Requests\Api\Key\KeyListRequest;
use App\Http\Requests\Api\Key\UpdateKeyRequest;
use App\Http\Resources\Api\Game\GameDetailKeyResource;
use App\Http\Resources\Api\GameHistory\GameHistoryResource;
use App\Models\Currency;
use App\Models\GameStockUpdate;
use App\Models\JarTransaction;
use App\Models\Key;
use App\Models\Order;
use App\Models\User;
use App\Traits\AccountSummaryTrait;
use App\Traits\ApiTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use JetBrains\PhpStorm\ArrayShape;

class KeyService
{

    public const WARN_EUR_LIMIT = 100;
    public const LIMIT_WARN_CODE = 1001;

    use ApiTrait, AccountSummaryTrait;

    public static function defineAccordingOrderStatus(int $orderStatus): int|string
    {
        return match ($orderStatus) {
            OrderUpdateStatus::DECLINE->value => KeyStatus::ACTIVE->value,
            OrderUpdateStatus::APPROVE->value => KeyStatus::SOLD->value,
            default => 'Belirlenemedi'
        };


    }

    #[ArrayShape(['game_id' => "mixed", 'supplier_id' => "mixed", 'cost' => "float|mixed", 'cost_currency_id' => "mixed", 'cost_convert_euro' => "float|int", 'is_kdv' => "mixed", 'percent_kdv' => "int|mixed", 'kdv_amount' => "float|int"])]
    public function getDataWhenUpdateKey(UpdateKeyWithTransactionRequest $request): array
    {


        $costCurrency = Currency::findOrFail($request->input('currency'));

        $costWithoutKdv = $request->input('cost');

        $percentOfKdv = $request->input('percent_of_kdv');

        $kdvAmount = KeyService::calculateKdvAmount($costWithoutKdv, $percentOfKdv);

        $costWithKdv = $costWithoutKdv + $kdvAmount;
        $costConvertEuro = CurrencyService::convertEur($costWithKdv, $costCurrency);

        return [
            'game_id' => $request->input('game_id'),
            'supplier_id' => $request->input('supplier_id'),
            'cost' => $costWithKdv,
            'cost_currency_id' => $request->input('currency'),
            'cost_convert_euro' => $costConvertEuro,
            'is_kdv' => $request->input('is_kdv'),
            'percent_kdv' => !$request->input('is_kdv') ? 0 : $request->input('is_kdv'),
            'kdv_amount' => !$request->input('is_kdv') ? 0 : $kdvAmount
        ];
    }

    public static function calculateCostFromKeys($collection)
    {
        return $collection->sum('cost');
    }

    public static function defineCustomer($keyResource)
    {

        /** @var Order $order */
        $order = $keyResource?->order;


        if ($order) {
            $orderType = (int)$keyResource->order->order_type->value;

            if (OrderTypeEnum::FROM_API->value == $orderType) {

                return KeyService::defineApiCustomerWithCode($order->order_code);

            } elseif (OrderTypeEnum::TO_CUSTOMER->value == $orderType) {

                return $keyResource->order->customer->name;
            }
        } else {
            return 'no customer';
        }
        return 'none';
    }

    public static function defineApiCustomerWithCode($code): string
    {
        return match (true) {
            str_contains($code, MarketplacePrefix::ENEBA->value) => 'Eneba Customer',
            str_contains($code, MarketplacePrefix::GAMIVO->value) => 'Gamivo Customer',
            str_contains($code, MarketplacePrefix::KINGUIN->value) => 'Kinguin Customer',
            str_contains($code, MarketplacePrefix::K4G->value) => 'K4G Customer',
            str_contains($code, '-ga2') => 'G2A Customer',
            str_contains($code, 'O-et11') or str_contains($code, MarketplacePrefix::ETAIL->value) => 'Etail Customer',
            default => 'none'
        };
    }


    public static function getSellPrice(GameDetailKeyResource $keyResource): string
    {
        $order = $keyResource->order;

        if ($order and $keyResource->status == KeyStatus::SOLD->value) {
            return ($keyResource->saleInfo?->amount ?? '0') . ($keyResource->saleInfo?->currencyInfo->symbol ?? '€');
        }
        return Key::NOT_SELL;
    }

    public function lastTenGameProcess(): AnonymousResourceCollection
    {
        $histories = GameStockUpdate::orderByDesc('created_at')->limit(20)->with(['game' => function ($query) {
            $query->with('publisher:id,name')->select(['id', 'uuid', 'name', 'publisher_id']);
        }, 'supplier:id,name'])->get();


        return GameHistoryResource::collection($histories);
    }

    public function createKeyCode(string $prefix): string
    {
        $keyCode = uniqid($prefix);
        do {
            $keyCode = uniqid($prefix);
        } while (Key::where('key', $keyCode)->exists());

        return $keyCode;
    }

    public static function createReservationId(int $length = 10): string
    {
        $reservationId = Str::random($length);
        do {
            $reservationId = Str::random($length);
        } while (Order::where('reservation_id', $reservationId)->exists());

        return $reservationId;
    }


    /**
     * @param $start
     * @param $end
     * @return JsonResponse|array{
     *     cost:string,
     *     giro:string,
     *     profit:string
     *      }
     */
    public function getSummary($start, $end = null): \Illuminate\Http\JsonResponse|array
    {
        //$start = now()->startOfMonth()->format('Y-m-d H:i:s');

        try {
            $start = Carbon::parse($start)->format('Y-m-d H:i:s');
            if (!is_null($end)) {
                $end = Carbon::parse($end)->format('Y-m-d H:i:s');
            }
        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }

        $result = Key::where('sell_date', '>=', $start)
            ->when(!is_null($end), function ($query) use ($end) {
                $query->where('sell_date', '<=', $end);
            })
            ->whereStatus(KeyStatus::SOLD->value)
            ->join('sale_infos', 'keys.sale_info_id', 'sale_infos.id')
            ->select(DB::raw('SUM(keys.cost_convert_euro) as output, SUM(sale_infos.amount_convert_euro) as input '))->first();

        $out = $result->output ?? 0;
        $in = $result->input ?? 0;
        $profit = $in - $out;
        $addExpense = ExpenseService::getTotalExpenseByDate($start, $end);

        $totalCost = $out + $addExpense;

        $data = [
            'cost' => $out,
            'add_expense' => $addExpense,
            'total_cost' => $totalCost,
            'giro' => $in,

            'profit' => $profit,
            'net_profit' => $in - $totalCost
        ];

        return PriceService::toStringArrayItems($data);

    }


    /**
     * @param $start
     * @param $end
     * @return JsonResponse|array{
     *     cost:float,
     *     giro:float,
     *     profit:float
     * }
     */
    public function getSummaryForTable($start, $end): array
    {

        $data = $this->getSummary($start, $end);

        foreach ($data as $index => $datum) {
            $data[$index] = PriceService::convertStrToFloat(str_replace([',', '€'], '', $datum));
        }

        return $data;
    }


    public function getSummaryKdv($start, $end = null): \Illuminate\Http\JsonResponse|int|string
    {

        try {
            $start = Carbon::parse($start)->format('Y-m-d H:i:s');
            if (!is_null($end)) {
                $end = Carbon::parse($end)->format('Y-m-d H:i:s');
            }
        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }

        $result = Key::where('sell_date', '>=', $start)
            ->when(!is_null($end), function ($query) use ($end) {
                $query->where('sell_date', '<=', $end);
            })
            ->whereStatus(KeyStatus::SOLD->value)
            ->select(DB::raw('SUM(keys.kdv_amount) as total_kdv'))->first();


        return PriceService::convertFloatForFront($result->total_kdv ?? 0);

    }

    public function getListByFilterPayload(array $payload): Builder|\LaravelIdea\Helper\App\Models\_IH_Key_QB
    {
        $request = new KeyListRequest($payload);


        return Key::query()
            ->with([
                'game:id,name,uuid',
                'supplier:id,name',
                'costCurrency:id,symbol',
                'creator:id,name',
                'saleInfo',
                'order' => [
                    'match:id,marketplace_id',
                    'amountCurrency:id,symbol'
                ],
            ])
            ->when($request->input('start'), function (Builder $builder) use ($request) {
                $builder->whereDate('created_at', '>=', \Illuminate\Support\Carbon::parse($request->input('start')));
            })
            ->when($request->input('end'), function (Builder $builder) use ($request) {
                $builder->whereDate('created_at', '<=', Carbon::parse($request->input('end')));
            })
            ->when(($request->input('status')) && ($request->input('status') !== -1), function (Builder $query) use ($request) {
                $query->when($request->input('status') == KeyStatus::DELETED->value, function (Builder $query) {
                    $query->withTrashed();
                })->where('status', $request->input('status'));
            })
            ->when($request->filled('key_code'), function ($query) use ($request) {
                $search = trim($request->input('key_code'));
                $query->where('key', 'like', "%$search%");
            })
            ->when($request->input('game_id'), function (Builder $query) use ($request) {
                $query->where('game_id', $request->input('game_id'));
            })
            ->when($request->input('supplier_id'), function (Builder $query) use ($request) {
                $query->where('supplier_id', $request->input('supplier_id'));
            })
            ->when($request->input('customer_id'), function (Builder $query) use ($request) {
                $query->whereRelation('order.customer', 'id', '=', $request->input('customer_id'));
            })
            //craeted at
            ->when(($request->input('order_by_created')) and $request->input('order_by_created') == OrderDataEnum::ASC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::ASC->value);
            })
            ->when(($request->input('order_by_created')) and $request->input('order_by_created') == OrderDataEnum::DESC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::DESC->value);
            })
//                //sell date
            ->when(($request->input('order_by_sell_date')) and $request->input('order_by_sell_date') == OrderDataEnum::ASC->value, function ($query) {

                $query->orderBy('created_at', OrderDataEnum::ASC->value);
            })
            ->when($request->input('order_by_sell_date') and $request['order_by_sell_date'] == OrderDataEnum::DESC->value, function ($query) {
                $query->orderBy('created_at', OrderDataEnum::DESC->value);
            });

    }

    public function deleteKey($keyIds)
    {
        $keyStatusHistoryService = new KeyStatusHistoryService();

        $keyIds = is_array($keyIds) ? $keyIds : [$keyIds];

        return DB::transaction(function () use ($keyStatusHistoryService, $keyIds) {

            $pre = Key::PRE_DEL;

            DB::table('keys')->whereIn('id', $keyIds)->update([
                'status' => KeyStatus::DELETED->value,
                'key' => DB::raw("CONCAT('$pre',keys.key)")
            ]);
            Key::whereKey($keyIds)->delete();

            return true;
        });
    }


    /**
     * @param string|int $marketPlaceId
     * @return array An associative array containing sales data.
     *   - 'hourly' int: The hourly sales count.
     *   - 'daily' int: The daily sales count.
     *   - 'monthly' int: The monthly sales count.
     */
    public function getSaleByMarketplace(string|int $marketPlaceId, $gameId): array
    {
        $hourlyCount = Key::getSoldByDates(now()->subHour())->whereRelation('order.match', 'marketplace_id', $marketPlaceId)->when(!is_null($gameId), function (Builder $query) use ($gameId) {
            $query->where('game_id', $gameId);
        })->count();
        $dailyCount = Key::getSoldByDates(today()->startOfDay())->whereRelation('order.match', 'marketplace_id', $marketPlaceId)->when(!is_null($gameId), function (Builder $query) use ($gameId) {
            $query->where('game_id', $gameId);
        })->count();
        $monthlyCount = Key::getSoldByDates(today()->startOfMonth())->whereRelation('order.match', 'marketplace_id', $marketPlaceId)->when(!is_null($gameId), function (Builder $query) use ($gameId) {
            $query->where('game_id', $gameId);
        })->count();


        return [
            'hourly' => $hourlyCount,
            'daily' => $dailyCount,
            'monthly' => $monthlyCount
        ];
    }

    /**
     * @param $start
     * @param $end
     * @return array{
     *     year_id:integer,
     *     month_id:integer,
     *     month_name:string,
     *     date:Carbon,
     *     total_margin:float,
     *     total_cost:float,
     *     total_giro:float,
     *     net_margin:float,
     *     add_cost:float,
     *     by_category_info:string
     * }
     */
    public function getLastMonthSummary(): array
    {
        $month = (now()->month) - 1;
        $year = (now()->year);

        $monthName = Carbon::createFromDate($year, $month)->locale('tr')->isoFormat('MMMM');

        $start = Carbon::createFromDate($year, $month)->startOfMonth();
        $end = Carbon::createFromDate($year, $month)->endOfMonth();

        $data = [
            'year_id' => $year,
            'month_id' => $month,
            'month_name' => $monthName,
            'date' => $start,
        ];

        $summaryData = $this->getSummaryForTable($start, $end);
        $data = array_merge($data,
            [
                'total_margin' => $summaryData['profit'],
                'total_cost' => $summaryData['cost'],
                'total_giro' => $summaryData['giro'],
                'net_margin' => 0,
                'add_cost' => 0,
                'by_category_info' => json_encode($this->byCategory($month, $year))
            ]
        );

        return $data;
    }

    public function getErrorSalesByPeriod(int $period)
    {
        $dates = $this->defineDatesByPeriod($period);

        return Key::join('sale_infos', 'keys.sale_info_id', '=', 'sale_infos.id')
            ->join('users', 'users.id', '=', 'keys.who')
            ->getSold()
            ->when(!is_null($dates['start']), function (Builder $query) use ($dates) {
                $query->where('sell_date', '>=', $dates['start']);
            })
            ->when(!is_null($dates['end']), function (Builder $query) use ($dates) {
                $query->where('sell_date', '<=', $dates['end']);
            })
            ->whereRaw('(sale_infos.amount_convert_euro - keys.cost_convert_euro) < 0')
            ->select([
                'keys.id',
                DB::raw('keys.key'),
                DB::raw('keys.cost_convert_euro'),
                DB::raw('sale_infos.amount_convert_euro'),
                DB::raw('(sale_infos.amount_convert_euro - keys.cost_convert_euro) as between1'),
                DB::raw('keys.created_at'),
                DB::raw('keys.sell_date'),
                DB::raw('users.first_name'),
            ]);


    }


    //#[ArrayShape(["end" => "mixed|null", "start" => "mixed|null"])]

    /**
     * @param int $period
     * @return array{
     *           start : string,
     *           end: string
     *          }
     */
    public function defineDatesByPeriod(int $period): array
    {
        $year = now()->year;
        $month = (now()->month) - 1;

        return match ($period) {

            SalesPeriod::TODAY_SALES->value => ['start' => now()->startOfDay()->format('Y-m-d H:i:s'), 'end' => null],

            SalesPeriod::YESTERDAY_SALES->value => [
                'start' => now()->subDay()->startOfDay()->format('Y-m-d H:i:s'),
                'end' => now()->subDay()->endOfDay()->format('Y-m-d H:i:s')
            ],

            SalesPeriod::THIS_WEEK_SALES->value => [
                'start' => now()->startOfWeek()->format('Y-m-d H:i:s'),
                'end' => now()->endOfWeek()->format('Y-m-d H:i:s')
            ],

            SalesPeriod::THIS_MONTH_SALES->value => [
                'start' => now()->startOfMonth()->format('Y-m-d H:i:s'),
                'end' => now()->endOfMonth()->format('Y-m-d H:i:s')
            ],

            SalesPeriod::THIS_YEAR_SALES->value => [
                'start' => now()->startOfYear()->format('Y-m-d H:i:s'),
                'end' => null
            ],

            SalesPeriod::LAST_WEEK_AND_THIS_WEEK_SALES->value => [
                'start' => now()->subDays(14)->format('Y-m-d H:i:s'),
                'end' => null
            ],
            SalesPeriod::LAST_MONTH_SALES->value => [
                'start' => Carbon::create($year, $month)->startOfMonth()->format('Y-m-d H:i:s'),
                'end' => Carbon::create($year, $month)->endOfMonth()->format('Y-m-d H:i:s')
            ],
            default => [
                'start' => null,
                'end' => null
            ]
        };
    }

    public static function calculateCostByKdv(float $costWithoutKdv, float $percentOfKdv = null): float
    {
        if (!$percentOfKdv or ($percentOfKdv == 0)) {
            return $costWithoutKdv;
        }

        return PriceService::convertFloat(($percentOfKdv / $costWithoutKdv) * $costWithoutKdv);


    }

    public static function calculateKdvAmount(float $costWithoutKdv, float $percentOfKdv = null): float
    {
        if (!$percentOfKdv or ($percentOfKdv == 0)) {
            return 0;
        }
        return PriceService::convertFloat(($costWithoutKdv * $percentOfKdv) / 100);
    }


    public function defineKeyInfoForUpdate(Key $key): array
    {
        return [
            'supplier_id' => $key->supplier_id,
            'game_id' => $key->game_id,
            'buys_in_sametime' => $key->buysInSameTime,
            'status' => $key->status,
            'cost_convert_eur' => $key->cost_convert_euro,
            'cost_currency' => $key->cost_currency_id,
            'cost_amount' => $key->cost,
            'supplier_jar' => $key->supplier->jar
        ];
    }


    public function isThereTransactionProcess(UpdateKeyRequest|UpdateKeyWithTransactionRequest $request, Key $key): bool
    {
        return ($key->cost_currency_id != $request->input('currency')) or ($key->cost != $request->input('cost')) or ($key->supplier_id != $request->input('supplier_id')) or ($key->is_kdv != $request->input('is_kdv')) or ($key->percent_kdv != $request->input('percent_of_kdv'));
    }

    public function updateKeyInError(Key $key, UpdateKeyRequest $request): void
    {

        if ($this->isThereTransactionProcess($request, $key)) {

            $costCurr = $request->input('currency');

            $costCurrency = Currency::findOrFail($costCurr);
            $costWithoutKdv = $request->input('cost');
            $costConvertEuro = CurrencyService::convertEur($costWithoutKdv, $costCurrency);

            $costCurrencyId = $request->input('currency');
            $updateData = $request->except(['user_is_sure', 'description', 'currency']);

            $updateData = array_merge($updateData, ['cost_convert_euro' => $costConvertEuro, 'cost_currency_id' => $costCurrencyId]);

            $key->update($updateData);

        } else {
            $key->update($request->validated());
        }
    }

    public function expenseAndIncomeToUser(User $toSupplier, JarTransaction $currentTransaction, User $currentSupplier, array $updateData, JarTransactionService $jarTransactionService, $minBalance = null): JsonResponse
    {
        if (!is_null($minBalance)) {
            $willBeTakeAgain = $jarTransactionService->createNewJarTransactionWithKeys(collect($currentTransaction->keys), $currentSupplier->jar->currency);


            $afterAgainBalance = $currentSupplier->jar->balance + $willBeTakeAgain;


            $willBeUpdateKeys = $currentTransaction->keys->map(function (Key $key) use ($updateData) {

                $key->cost = $updateData['cost'];
                $key->cost_currency_id = $updateData['cost_currency_id'];
                $key->is_kdv = $updateData['is_kdv'];
                $key->kdv_amount = $updateData['kdv_amount'];
                $key->percent_kdv = $updateData['percent_kdv'];
                $key->cost_convert_euro = $updateData['cost_convert_euro'];
                return $key;
            });


            $afterAgainIncome = $jarTransactionService->createNewJarTransactionWithKeys($willBeUpdateKeys, $currentSupplier->jar->currency);


            $willBeBalance = $afterAgainBalance - $afterAgainIncome;


            if ($willBeBalance < $minBalance) {
                app()->setLocale('tr');
                return $this->returnWithMessage(__('orders.min_balance_warning', ['minBalance' => $minBalance, 'willBeBalance' => $willBeBalance]));
            }
        }

        $oneKey = $currentTransaction->keys->first();


        $result = DB::transaction(function () use ($oneKey, $toSupplier, $currentTransaction, $currentSupplier, $updateData, $jarTransactionService) {
            //adama parsını geri ver
            $inComeTransaction = $jarTransactionService->takeExpenseFromSupplier($currentSupplier, $currentTransaction, auth()->id());
            $jarTransactionService->createTransaction($inComeTransaction);
            $currentTransaction->keys()->update($updateData);


            //admdan yeni güncellemey göre para al
            $transactionId = $jarTransactionService->addExpenseAgain($currentTransaction->keys);


            $currentTransaction->keys()->update([
                'jar_transaction_id' => $transactionId
            ]);

            app(GameService::class)->updateStock($updateData['game_id']);

            if ($oneKey->game_id != $updateData['game_id']) {
                app(GameService::class)->updateStock($oneKey->game_id);
            }
            return true;
        });

        if ($result) {
            return $this->apiSuccessResponse(null, Response::HTTP_OK, 'Güncelleme başarılı!');
        }

        return $this->apiSuccessResponse(__('orders.went_wrong'));
    }

}
