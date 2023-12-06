<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Enums\KeyStatus;
use App\Enums\MarketplaceName;
use App\Enums\OrderDataEnum;
use App\Enums\RoleEnum;
use App\Http\Requests\Api\Game\ListGameRequest;
use App\Http\Requests\Api\Game\StockListRequest;
use App\Http\Requests\Api\Marketplace\MatchMultipleMarketPlaceRequest;
use App\Models\Game;
use App\Models\GameStockUpdate;
use App\Models\Key;
use App\Models\MarketplaceMatchGame;
use App\Models\User;
use App\Notifications\NotifyAboutStock;
use App\Traits\ApiTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use JetBrains\PhpStorm\ArrayShape;

class GameService
{
    use ApiTrait;


//    #[ArrayShape(['active_keys_count' => "int"])]
//    public static function getStockKeys(string|int $gameId): array
//    {
//        return [
//            'active_keys_count' => Key::whereGameId($gameId)->whereStatus(KeyStatus::ACTIVE->value)->count()
//        ];
//    }

    public function realMatchData(MatchMultipleMarketPlaceRequest $request)
    {
        $gamId = $request->input('game_id');
        $status = $request->input('status');

        $enebaMatchProduct = $this->payload['eneba_product'] ?? null;
        $kinguinMatchProduct = $this->payload['kinguin_product'] ?? null;
        $gamivoMatchProduct = $this->payload['gamivo_product'] ?? null;
        $k4gMatchProduct = $this->payload['k4g_product'] ?? null;

        $res = [];


        if ($enebaMatchProduct) {
            $res [] = [
                'game_id' => $gamId,
                'status' => $status,
                'product_api_id' => $enebaMatchProduct,
                'marketplace_id' => MarketplaceName::ENEBA->value
            ];
        }

        if ($kinguinMatchProduct) {
            $res [] = [
                'game_id' => $gamId,
                'status' => $status,
                'product_api_id' => $enebaMatchProduct,
                'marketplace_id' => MarketplaceName::KINGUIN->value
            ];
        }

        if ($gamivoMatchProduct) {
            $res [] = [
                'game_id' => $gamId,
                'status' => $status,
                'product_api_id' => $enebaMatchProduct,
                'marketplace_id' => MarketplaceName::GAMIVO->value
            ];
        }

        if ($k4gMatchProduct) {
            $res [] = [
                'game_id' => $gamId,
                'status' => $status,
                'product_api_id' => $enebaMatchProduct,
                'marketplace_id' => MarketplaceName::K4G->value
            ];

        }

        return $res;
    }

    public static function getStocksByGameId(string|int $gameId): int
    {
        return self::getStockKeys($gameId)['active_keys_count'];
    }


    #[ArrayShape(['id' => "\Illuminate\Support\HigherOrderCollectionProxy|int|mixed", 'active_keys_count' => "\Illuminate\Support\HigherOrderCollectionProxy|int|mixed", 'name' => "\Illuminate\Support\HigherOrderCollectionProxy|mixed|string", 'amount' => "float|\Illuminate\Support\HigherOrderCollectionProxy|mixed"])] public static function getStockKeys(string|int $gameId): array
    {
        $data = Game::select(['id', 'name', 'amount'])->withCount('activeKeys')->find($gameId);

        return [
            'id' => $data->id,
            'active_keys_count' => $data->active_keys_count,
            'name' => $data->name,
            'amount' => $data->amount
        ];
    }

    public static function getOnlyStock($gameId): int
    {
        return Key::whereGameId($gameId)->whereStatus(KeyStatus::ACTIVE->value)->count();
    }

    public static function getMatchesMarketPlace(string|int $gameId): array
    {
        $matches = MarketplaceMatchGame::where('game_id', $gameId)->with('marketPlace')->get();

        $result = [];

        foreach ($matches as $match) {
            $result[$match->marketPlace->name] = $match->offer_id;
        }

        echo json_encode($result) . PHP_EOL;

        return $result;
    }

    public function saveGameProcess(int $oldStock, int $newStock, string|int $gameId, string|int $supplierId): void
    {
        GameStockUpdate::create([
            'game_id' => $gameId,
            'supplier_id' => $supplierId,
            'new_stock' => $newStock,
            'old_stock' => $oldStock,
            'qty' => $newStock - $oldStock,
            'date' => now()
        ]);
    }

    public function updateStock(string|int $gameId): void
    {
        Game::find($gameId)?->update(['stock' => Key::whereGameId($gameId)->whereStatus(KeyStatus::ACTIVE->value)->count()]);
    }

    public function getSoldSummary($start, $end = null)
    {
        try {
            $start = Carbon::parse($start)->format('Y-m-d H:i:s');
            if (!is_null($end)) {
                $end = Carbon::parse($end)->format('Y-m-d H:i:s');
            }

            return Key::select(
                [
                    'keys.game_id',
                    DB::raw('COUNT(keys.game_id) as sale'),
                    'games.name as game_name',
                    'games.uuid as game_uuid',
                    'games.stock'
                ])
                ->join('games', 'games.id', 'keys.game_id')
                ->getSoldByDates($start, $end)
                ->groupBy('keys.game_id')
                ->orderByDesc('sale')
                ->limit(20)
                ->get();
        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    public function getGameForPanel(string|int $customerId)
    {
        $customer = User::findOrFail($customerId);
        $filter = json_decode($customer->customer_filter ?? "[]", 1);

        return Game::with(['offers', 'language:id,name', 'region:id', 'category:id,name'])
            ->whereStatus(GameStatus::ACTIVE->value)
            ->when($this->checkFilter($filter, 'regions'), function (Builder $query) use ($filter) {
                $query->whereIn('region_id', $filter['regions']);
            })
            ->when($this->checkFilter($filter, 'categories'), function (Builder $query) use ($filter) {
                $query->whereIn('category_id', $filter['categories']);
            })
            ->when($this->checkFilter($filter, 'category_types') and count($filter['category_types']), function (Builder $query) use ($filter) {
                $query->whereIn('category_type', $filter['category_types']);
            })
            ->where('stock', '>=', 1)
            ->orderByDesc('created_at');
    }

    protected function checkFilter($array, $index): bool
    {
        return isset($array[$index]) and is_array($array[$index]) and count($array[$index]);
    }

    public static function notifyUserAboutStock(int $stock, array $stockData, $gameId, MarketplaceName $marketplace): void
    {
        if ($stock < 10) {

            $users = User::role(RoleEnum::MARKETING->value)->get();

            if (!$users->count()) {
                $userEmails = ['eser@cdkeyci.com', 'yigithan.demircin@5deniz.com', 'alperen.bekdogdu@5deniz.com'];
                $us = User::whereIn('email', $userEmails)->get();

                foreach ($us as $u) {
                    $u->assignRole(RoleEnum::MARKETING->value);
                }
            }

            $message = "{$stockData['name']} için stok: $stock";
            Notification::send(User::query()->role(RoleEnum::MARKETING->value)->get(), new NotifyAboutStock($marketplace, $message, $gameId, $stock));
        }
    }

    public function listAll(array $payload)
    {
        $request = new ListGameRequest($payload);

        return Game::with(['category:id,name', 'publisher:id,name', 'region:id,name', 'language:id,name', 'matchActives', 'matchPassives', 'matchesMarketPlaces:id', 'autoMarketplaces:id,name'])
            ->withAvg('activeKeys', 'cost_convert_euro')
            ->withCount('activeKeys')
            ->when($request->filled('search_game'), function (Builder $builder) use ($request) {
                $search = $request->input('search_game');
                $builder->where('name', 'like', '%' . trim($search) . '%');
            })
            ->when($request->filled('game_status'), function (Builder $builder) use ($request) {
                $builder->where('status', $request->input('game_status'));
            })
            ->when($request->filled('publishers'), function (Builder $builder) use ($request) {
                $builder->whereIn('publisher_id', $request->input('publishers'));
            })
            ->when($request->filled('categories'), function (Builder $builder) use ($request) {
                $builder->whereIn('category_id', $request->input('categories'));
            })
            // todo buraya bakabilirsin
            ->when($request->filled('marketplaces'), function (Builder $builder) use ($request) {
                $marketplaces = $request->input('marketplaces');
                $matchStatus = $request->input('market_place_status');

                $builder->whereHas('matchesMarketPlaces', function ($builder) use ($marketplaces) {
                    $builder->whereIn('market_places.id', $marketplaces);
                })->when(!is_null($matchStatus), function (Builder $builder) use ($marketplaces, $matchStatus) {
                    $builder->when(($matchStatus) == 1, function (Builder $builder) use ($marketplaces) {
                        $builder->whereHas('matchActives', function (Builder $builder) use ($marketplaces) {
                            $builder->whereIn('market_places.id', $marketplaces);
                        });
                    }, function (Builder $builder) use ($marketplaces) {
                        $builder->whereHas('matchPassives', function (Builder $builder) use ($marketplaces) {
                            $builder->whereIn('market_places.id', $marketplaces);
                        });
                    });

                });
            }, function (Builder $builder) use ($request) {
                $builder->when($request->filled('market_place_status'), function (Builder $builder) use ($request) {
                    $builder->whereHas('matchesMarketPlaces', function (Builder $builder) use ($request) {
                        $builder->where('status', $request->input('market_place_status'));
                    });
                });
            })
            ->when($request->filled('stock_available') && $request->input('stock_available') == 1, function (Builder $builder) {
                $builder->having('active_keys_count', '>=', 1);
                //stock için degis  $builder->where('stock', '>=', 1)->orderByDesc('stock');
            })
            ->when($request->input('order_by_updated') == OrderDataEnum::DESC->value, function (Builder $builder) {
                $builder->orderByDesc('price_updated_at');
            })
            ->when($request->input('order_by_updated') == OrderDataEnum::ASC->value, function (Builder $builder) {

                $builder->orderBy('price_updated_at');
            })
            ->when($request->input('order_by_stock') == OrderDataEnum::DESC->value, function (Builder $builder) {

                //stock için degis $builder->orderBy('stock', OrderDataEnum::DESC->value);
                $builder->orderBy('active_keys_count', OrderDataEnum::DESC->value);
            })
            ->when($request->input('order_by_stock') == OrderDataEnum::ASC->value, function (Builder $builder) {

                //stock için degis  $builder->orderBy('stock', OrderDataEnum::ASC->value);
                $builder->orderBy('active_keys_count', OrderDataEnum::ASC->value);
            })
            ->when($request->input('order_by_created') == OrderDataEnum::DESC->value, function (Builder $builder) {

                $builder->orderBy('created_at', OrderDataEnum::DESC->value);
            })
            ->when($request->input('order_by_created') == OrderDataEnum::ASC->value, function (Builder $builder) {
                $builder->orderBy('created_at', OrderDataEnum::ASC->value);
            });
    }

    public function listAll1(array $payload)
    {
        $request = new ListGameRequest($payload);

        return Game::with(['category:id,name', 'publisher:id,name', 'region:id,name', 'language:id,name', 'matchActives', 'matchPassives', 'matchesMarketPlaces:id', 'autoMarketplaces:id,name'])
            ->withAvg('activeKeys', 'cost_convert_euro')
//            ->withCount('activeKeys')
            ->when($request->filled('search_game'), function (Builder $builder) use ($request) {
                $search = $request->input('search_game');
                $builder->where('name', 'like', '%' . trim($search) . '%');
            })
            ->when($request->filled('game_status'), function (Builder $builder) use ($request) {
                $builder->where('status', $request->input('game_status'));
            })
            ->when($request->filled('publishers'), function (Builder $builder) use ($request) {
                $builder->whereIn('publisher_id', $request->input('publishers'));
            })
            ->when($request->filled('categories'), function (Builder $builder) use ($request) {
                $builder->whereIn('category_id', $request->input('categories'));
            })
            // todo buraya bakabilirsin
            ->when($request->filled('marketplaces'), function (Builder $builder) use ($request) {
                $marketplaces = $request->input('marketplaces');
                $matchStatus = $request->input('market_place_status');

                $builder->whereHas('matchesMarketPlaces', function ($builder) use ($marketplaces) {
                    $builder->whereIn('market_places.id', $marketplaces);
                })->when(!is_null($matchStatus), function (Builder $builder) use ($marketplaces, $matchStatus) {
                    $builder->when(($matchStatus) == 1, function (Builder $builder) use ($marketplaces) {
                        $builder->whereHas('matchActives', function (Builder $builder) use ($marketplaces) {
                            $builder->whereIn('market_places.id', $marketplaces);
                        });
                    }, function (Builder $builder) use ($marketplaces) {
                        $builder->whereHas('matchPassives', function (Builder $builder) use ($marketplaces) {
                            $builder->whereIn('market_places.id', $marketplaces);
                        });
                    });

                });
            }, function (Builder $builder) use ($request) {
                $builder->when($request->filled('market_place_status'), function (Builder $builder) use ($request) {
                    $builder->whereHas('matchesMarketPlaces', function (Builder $builder) use ($request) {
                        $builder->where('status', $request->input('market_place_status'));
                    });
                });
            })
            ->when($request->filled('stock_available') && $request->input('stock_available') == 1, function (Builder $builder) {
                $builder->having('active_keys_count', '>=', 1);
                //stock için degis  $builder->where('stock', '>=', 1)->orderByDesc('stock');
            })
            ->when($request->input('order_by_updated') == OrderDataEnum::DESC->value, function (Builder $builder) {
                $builder->orderByDesc('price_updated_at');
            })
            ->when($request->input('order_by_updated') == OrderDataEnum::ASC->value, function (Builder $builder) {

                $builder->orderBy('price_updated_at');
            })
            ->when($request->input('order_by_stock') == OrderDataEnum::DESC->value, function (Builder $builder) {

                //stock için degis $builder->orderBy('stock', OrderDataEnum::DESC->value);
                $builder->orderBy('stock', OrderDataEnum::DESC->value);
            })
            ->when($request->input('order_by_stock') == OrderDataEnum::ASC->value, function (Builder $builder) {

                //stock için degis  $builder->orderBy('stock', OrderDataEnum::ASC->value);
                $builder->orderBy('stock', OrderDataEnum::ASC->value);
            })
            ->when($request->input('order_by_created') == OrderDataEnum::DESC->value, function (Builder $builder) {

                $builder->orderBy('created_at', OrderDataEnum::DESC->value);
            })
            ->when($request->input('order_by_created') == OrderDataEnum::ASC->value, function (Builder $builder) {
                $builder->orderBy('created_at', OrderDataEnum::ASC->value);
            });
    }

    public function stockAll(array $payload)
    {
        $request = new StockListRequest($payload);
        return Game::with('publisher', 'matchActives:id')
            ->withSum('activeKeys', 'cost_convert_euro')
            //stock value
//            ->withCount('activeKeys')
            ->where('stock', '>=', 1)
//            ->having('active_keys_count', '>=', 1)
            ->when($request->filled('search_game'), function (Builder $builder) use ($request) {
                $search = $request->input('search_game');
                $builder->where('name', 'like', '%' . trim($search) . '%');
            })
            ->when($request->input('order_by_stock') == OrderDataEnum::DESC->value, function (Builder $builder) {
                $builder->orderBy('stock', OrderDataEnum::DESC->value);
            })
            ->when($request->input('order_by_stock') == OrderDataEnum::ASC->value, function (Builder $builder) {
                $builder->orderBy('stock', OrderDataEnum::ASC->value);
            })->when($request->input('order_by_created') == OrderDataEnum::DESC->value, function (Builder $builder) {
                $builder->orderBy('created_at', OrderDataEnum::DESC->value);
            })
            ->when($request->input('order_by_created') == OrderDataEnum::ASC->value, function (Builder $builder) {
                $builder->orderBy('created_at', OrderDataEnum::ASC->value);
            });
    }

    public function detail(Request $request): \Illuminate\Database\Eloquent\Model|Builder|Game|\LaravelIdea\Helper\App\Models\_IH_Game_QB
    {

        $perPage = request()->input('per_page') ?? config('general_settings.default_page');
        $currentPage = request()->input('current_page') ?? 1;

        return Game::with(['autoMarketplaces',
            'keys' => function ($query) use ($currentPage, $perPage, $request) {
            $query->when($request->filled('keyStatus'), function ($query) use ($request) {
                $query->whereStatus($request->input('keyStatus'));
            })->when($request->filled('keyCode'), function ($query) use ($request) {
                $keyCode = trim($request->input('keyCode'));
                $query->where('key', 'like', "%$keyCode%");
            })->when($request->input('order_by_created') == OrderDataEnum::DESC->value, function ($query) use ($request) {
                $query->orderByDesc('created_at');
            })->when($request->input('order_by_created') == OrderDataEnum::ASC->value, function ($query) use ($request) {
                $query->orderBy('created_at');
            })
                ->when(!$request->filled('order_by_created'), function ($query) use ($request) {
                    $query->orderByDesc('sell_date');
                })
                ->with(['supplier:id,name', 'costCurrency:id,symbol', 'saleInfo' => ['currencyInfo:id,symbol'], 'order' => ['customer']])->simplePaginate($perPage, ['*'], 'page', $currentPage);

        }])->where('uuid', $request->input('uuid'))->firstOrFail();
    }

}
