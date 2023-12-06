<?php

namespace App\Services;

use App\Enums\CurrencyEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\OfferStatus;
use App\Enums\RoleEnum;
use App\Jobs\Marketplace1\MakePassiveInMarketPlaceJob;
use App\Models\Game;
use App\Models\MarketplaceMatchGame;
use App\Enums\MarketplaceName;
use App\Interfaces\IMarketplace;
use App\Models\MarketPlace;
use App\Models\User;
use App\Notifications\UpdateStockNotification;
use App\Traits\ApiTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EnebaService implements IMarketplace
{
    use ApiTrait;

    public MarketPlace $relatedMarketPlace;


    private function check()
    {
        $relatedMarketPlace = MarketPlace::findByName(MarketplaceName::ENEBA->name)->first();

        if (!$relatedMarketPlace) {
            //todo burada marketplace yoksa log ve Notification yollansın gerekli kişilere yani bize
            $developers = User::role(RoleEnum::BACKEND_DEVELOPER->value)->get();
            $message = 'Eneba serviste ilişkili MarketPlace modeli bulunmadığı için hata!';
        }


        $token = $relatedMarketPlace->token;

        if (is_null($token)) {

            $token = self::getToken();

            $relatedMarketPlace->update(['token' => $token]);
        }

        $this->relatedMarketPlace = MarketPlace::findByName(MarketplaceName::ENEBA->name)->first();
    }

    public function getReservationByReservationId($item)
    {
        $reservationId = $item['reservation_id'];


        $myData = DB::connection('b2b_live')->table('reservation_contents')->where('reservation_id', $reservationId)->first();
        $reservation = DB::connection('b2b_live')->table('eneba_reserves')->where('reserve_id', $reservationId)->first();
        $reserveId = $reservation?->order_id;

        $d = DB::connection('b2b_live')->table('marketplace_api_games_match')->where('game_id', $myData->game_id)->where('api_id', 1)->get();

        if ($d->count() == 1) {
            return [$d[0]->game_id_in_api, $reserveId];
        } else {
            Log::error($item['order_code'] . ' =>>' . 'bunda match için birden fzla bulundu');
        }

    }

    public static function errorLog(): string
    {
        return MarketplaceName::ENEBA->name . '_error';
    }

    public static function successLog(): string
    {
        return MarketplaceName::ENEBA->name . '_success';
    }

    public function getApiUrl(): string
    {
        return 'https://api.eneba.com/';
    }

    public function getGraphqlUrl(): string
    {
        //return $this->getApiUrl() . 'graphql/';
        return $this->getApiUrl() . '/';
    }

    public const ERR_401 = 'Access to field denied';
    public const NOT_FOUND_FROM_ARRAY = 'not foun from array ';
    public const ALREADY_OFFER = 'because you already have auction with this product';
    public const ALREADY_UPDATED = 'Already and updated';


    public function getToken(): string
    {

        //$url = self::getApiUrl() . 'oauth/token';
        $url = 'https://user.eneba.com/' . 'oauth/token';

        $request = Http::asForm()->post($url, [
            'grant_type' => config('eneba.GRANT_TYPE'),
            'client_id' => config('eneba.CLIENT_ID'),
            'id' => config('eneba.ID'),
            'secret' => config('eneba.SECRET'),
        ]);

        if ($request->status() != 200) {
            $res = json_decode($request->body(), 1);
        }

        $data = json_decode($request->body(), 1);

        return $data['access_token'];
    }

    public function refreshToken(): void
    {
        $this->check();

        $token = self::getToken();
        $this->relatedMarketPlace->update(['token' => $token]);
    }


    public function searchGame(string $search): array
    {
        $this->check();

        $query = [
            'query' => '
                {
                S_products(
                    first: 100,
                    search: "' . $search . '"
                ) {
                    totalCount
                    pageInfo {
                        hasNextPage
                        hasPreviousPage
                        startCursor
                        endCursor
                    }
                    edges {
                        node {
                            id
                            name
                            releasedAt
                            createdAt
                            slug
                            type { value }
                            auctions(first: 1) {
                                edges {
                                    node {
                                        belongsToYou
                                        isInStock
                                        merchantName
                                        price { amount currency }
                                    }
                                }
                            }
                        }
                        cursor
                    }
                }
            }'
        ];

        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), $query);

        $error = $request->json()['errors'][0]['message'] ?? null;


        if ($error && str_contains($error, self::ERR_401)) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        $response = $request->json();


        $error = $request->json()['errors'][0]['message'] ?? null;

        if (is_null($error)) {
            $products = $response['data']['S_products']['edges'] ?? self::NOT_FOUND_FROM_ARRAY;

            if ($products == self::NOT_FOUND_FROM_ARRAY) {
                Log::channel(self::errorLog())->error('Oyun arama esnnasında 401 dışında bir hata oluştu.Uygun data gelmedi');
                Log::channel(self::errorLog())->error(json_encode($request->json()));
                return [];
            } else {
                $productData = [];
                foreach ($products as $product) {
                    $productData[] = [
                        'id' => $product['node']['id'],
                        'name' => $product['node']['name'],
                    ];
                }
                return $productData;
            }
        }

        return [];

    }


    public function getOffer($productId)
    {
        $this->check();

        $query = '{
        S_stock(productId: "' . $productId . '") {
            edges {
                node {
                    id
                    product { id name }
                    unitsSold
                    onHold
                    onHand
                    declaredStock
                    status
                    expiresAt
                    createdAt
                    autoRenew
                    price { amount currency }
                    position
                    priceUpdateQuota { quota nextFreeIn totalFree }
                }
            }
        }
    }';
        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        return $request->json();
    }


    public function getProductIdToOfferId($productId): mixed
    {
        $this->check();

        $query = '{
        S_stock(productId: "' . $productId . '") {
            edges {
                node {
                    id
                    product { id name }
                    unitsSold
                    onHold
                    onHand
                    declaredStock
                    status
                    expiresAt
                    createdAt
                    autoRenew
                    price { amount currency }
                    position
                    priceUpdateQuota { quota nextFreeIn totalFree }
                }
            }
        }
    }';
        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        Log::channel('fonksiyondan gelen');
        Log::channel('create_order_etail1')->info(json_encode($request->json()));


        return $request->json()['data']['S_stock']['edges'][0]['node'] ?? null;

    }


    public function getProductidFromOfferid(string $offerId)
    {
        $this->check();

        $query = '{
        S_stock(stockId: "' . $offerId . '") {
            edges {
                node {
                    id
                    product { id name }
                    unitsSold
                    declaredStock
                    status
                    expiresAt
                    createdAt
                    autoRenew
                    price { amount currency }
                    position
                    priceUpdateQuota { quota nextFreeIn totalFree }
                }
            }
        }
    }';
        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        $offer = $request->json();

        if ($offer !== null) {

            $productId = $offer['data']['S_stock']['edges'][0]['node']['product']['id'] ?? null;

            if (!$productId) return false;

            return $productId;
        }

        return false;
    }


    public function ifOfferExists($productId): array
    {
        $this->check();

        $query = '{
        S_stock(productId: "' . $productId . '") {
            edges {
                node {
                    id
                    product { id name }
                    unitsSold
                    onHold
                    onHand
                    declaredStock
                    status
                    expiresAt
                    createdAt
                    autoRenew
                    price { amount currency }
                    position
                    priceUpdateQuota { quota nextFreeIn totalFree }
                }
            }
        }
    }';

        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        $response = $request->json();

        if (empty($response['data']['S_stock']['edges'])) {
            return [];
        }

        return $response;
    }

    public function matchWithUs(array $payload, $userId = null): mixed
    {
        $notificationService = new NotificationService();
        $game = Game::findOrFail($payload['game_id']);
        $this->check();
        $payload['status'] = $payload['status'] ?? OfferStatus::ACTIVE->value;

        // Price'ı Euro cinsinden Cent'e dönüştürme
        $price = bcmul($payload['amount'], 100);

        $myCurrency = CurrencyEnum::from($payload['amount_currency'] ?? (CurrencyEnum::EUR->value));
        $myCurrencyName = $myCurrency->name;

        $stockData = GameService::getStockKeys($payload['game_id']);
        $gamStock = $stockData['active_keys_count'];
//        $declaredStock = ($payload['status'] === OfferStatus::PASSIVE->value or $gamStock == 0) ? 'null' : $gamStock;
        $declaredStock = $payload['status'] === OfferStatus::PASSIVE->value || $gamStock == 0 ? 'null' : ($payload['status'] ?? 'null');


        $query = [
            'query' => '
                 mutation {
                        S_createAuction(
                            input: {
                                productId: "' . $payload['product_api_id'] . '"
                                enabled: true
                                declaredStock: ' . $declaredStock . '
                                autoRenew: false
                                priceIWantToGet: { amount: ' . $price . ', currency: "' . $myCurrencyName . '" }
                            }
                        ){
                            success
                            actionId
                        }
                    }'
        ];


        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), $query);

        Log::channel('create_order_etail1')->info(json_encode($request->json()));
        Log::channel('create_order_etail1')->info(json_encode($payload));

        $error = $request->json()['errors'][0]['message'] ?? null;


        if ($error && str_contains($error, self::ERR_401)) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }
        $response = $request->json();


        $error = $response['errors'][0]['message'] ?? null;

        if ($error && str_contains($error, self::ERR_401)) { //tekrar refresh yaptıktan sonra 401 dönüyor ise bir şeyler yanlış gidiyor
            Log::channel(self::errorLog())->error(json_encode($response));
            return false;
        }


        if (is_null($error)) {
            return $response;//oluşturma başarılı
        } else {
            if (str_contains($error, self::ALREADY_OFFER)) {
                //get exists offer
                $existsOffer = $this->ifOfferExists($payload['product_api_id']);
                $existsOfferId = $existsOffer['data']['S_stock']['edges'][0]['node']['id'] ?? -1;
                if ($existsOfferId === -1) {
                    Log::channel(self::errorLog())->error("Enebada oyun eşleştirme yapılırken daha önce var olarak tespit edildi fakat offerId ye ulaşılamadı");
                    return false;
                } else {
                    // var olanı id sini almak başarılı ise update yap
                    $updateExistsOffer = $this->updateOffer([
                        'game_id' => $payload['game_id'],
                        'price' => $payload['amount'],
                        'offer_id_api' => $existsOfferId,
                        'status' => $payload['status']
                    ]);

                    if ($updateExistsOffer) {
                        return ['already_data' => $existsOfferId, 'message' => self::ALREADY_UPDATED];
                    } else {
                        $message = "{$game->name} Eneba'da eşlenirken offer tespit edildi fakat var olan güncellenemedi!";
                        Log::channel(self::errorLog())->error($message);
                        return false;
                    }


                }
            }
            //diğer hatalar
        }
        return false;
    }


    public function updateOffer(array $payload): bool
    {
        $this->check();

        //auctionId
        $offerIdEneba = $payload['offer_id_api'];

        // Price'ı Euro cinsinden Cent'e dönüştürme
        //$price = bcmul($payload['price'], 100);


        $stockData = GameService::getStockKeys($payload['game_id']);
        $gamStock = $stockData['active_keys_count'];

        //gameden gelir
        $price = bcmul($stockData['amount'], 100);


        $declaredStock = ($payload['status'] === OfferStatus::PASSIVE->value or $gamStock == 0) ? 'null' : $gamStock;

        $query = [
            'query' => '
             mutation {
                    S_updateAuction(
                        input: {
                            id: "' . $offerIdEneba . '"
                            enabled: true
                            autoRenew: false
                            declaredStock: ' . $declaredStock . '
                            priceIWantToGet: { amount: ' . $price . ', currency: "EUR" }
                        }
                    ){
                        success
                        actionId
                    }
                }'
        ];

        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), $query);

        $response = $request->json();

        $error = $response['errors'][0]['message'] ?? null;


        if ($error && str_contains($error, self::ERR_401)) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }
        $response = $request->json();

        $error = $response['errors'][0]['message'] ?? null;


        if ($error) {
            Log::channel(self::errorLog())->error("Eneba da oyun gencellenirken hata oluştu!´");
            Log::channel(self::errorLog())->error(json_encode($response));
            return false;
        }

        $game = Game::findOrFail($payload['game_id']);

        $message = "{$game->name} için Enebada stok güncelleme başarılı.Stock : $declaredStock ( $offerIdEneba )";

        Log::channel(self::successLog())->info($message);


        return true;
    }

    public function enableDeclaredStock(): mixed
    {
        $this->check();

        $query = [
            'query' => '
             mutation {
              P_enableDeclaredStock {
                success
                failureReason
              }
            }'
        ];


        $url = self::getGraphqlUrl();

        $response = Http::withToken($this->relatedMarketPlace->token)->post($url, $query);

        if ($response->failed()) {
            self::refreshToken();
            $response = Http::withToken($this->relatedMarketPlace->token)->post($url, $query);
        }

        $mutationResult = json_decode($response->body(), true);


        if (isset($mutationResult['errors'])) {
            foreach ($mutationResult['errors'] as $error) {
                return [
                    'error' => 1,
                    'message' => 'Declared Stock enabled failed: ' . $error['message'],
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR,
                ];
            }
        }

        if (!$mutationResult['data']['P_enableDeclaredStock']['success']) {
            return [
                'error' => 1,
                'message' => 'Failure Reason: ' . $mutationResult['data']['P_enableDeclaredStock']['failureReason'],
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }

        return $mutationResult;
    }


    public function changeStatus($offerIdInApi, $status, $stock = 0): bool
    {
        $this->check();

        if (!$status) {
            $stock = 0;
        } else {
            $matchData = MarketplaceMatchGame::where('offer_id', $offerIdInApi)->firstOrFail();
            $stockData = GameService::getStockKeys($matchData->game_id);
            $stock = $stockData['active_keys_count'];
        }


        if ($status == 1 and $stock == 0) {//stoğu sıfır olnaı aktif yapmamma
            $queryString = '
                    mutation {
                      S_updateAuction(
                        input: {
                          id: "' . $offerIdInApi . '"
                          declaredStock: ' . 'null' . '
                        }
                      ) {
                        success
                        actionId
                      }
                    }';
        }

        if ($status == 1 and $stock > 0) {
            $queryString = '
                    mutation {
                      S_updateAuction(
                        input: {
                          id: "' . $offerIdInApi . '"
                          declaredStock: ' . $stock . '
                        }
                      ) {
                        success
                        actionId
                      }
                    }';
        }
        if ($status == 0) {
            $queryString = '
                    mutation {
                      S_updateAuction(
                        input: {
                          id: "' . $offerIdInApi . '"
                          declaredStock: ' . 'null' . '
                        }
                      ) {
                        success
                        actionId
                      }
                    }';
        }


        $query = ['query' => $queryString];


        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), $query);


        $error = $response['errors'][0]['message'] ?? null;


        if ($error && str_contains($error, self::ERR_401)) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }
        $response = $request->json();


        $error = $response['errors'][0]['message'] ?? null;


        if ($error) {
            Log::channel(self::errorLog())->error("Eneba da oyun gencellenirken hata oluştu!´");
            Log::channel(self::errorLog())->error(json_encode($response));
            return false;
        }


        return true;


    }


    public function updateStock($offerId, $gameId): bool
    {
        $this->check();

        $stockData = GameService::getStockKeys($gameId);
        $price = bcmul($stockData['amount'], 100);
        $stock = $stockData['active_keys_count'];

        GameService::notifyUserAboutStock($stock, $stockData, $gameId, MarketplaceName::ENEBA);

        dispatch(new MakePassiveInMarketPlaceJob($gameId, $stock, MarketplaceName::ENEBA->value));

        $game = MarketplaceMatchGame::with('game', 'marketplace')->where('offer_id', $offerId)->firstOrFail();

        $query = [
            'query' => '
                    mutation {
                      S_updateAuction(
                        input: {
                          id: "' . $offerId . '"
                          declaredStock: ' . $stock . '
                          priceIWantToGet: { amount: ' . $price . ', currency: "EUR" }
                        }
                      ) {
                        success
                        actionId
                      }
                    }'
        ];


        $updateStockRequest = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), $query);

        $match = MarketplaceMatchGame::where('offer_id', $offerId)->with('game')->firstOrFail();

        $response = $updateStockRequest->json();

        if (isset($response['data']) and isset($response['data']['S_updateAuction']) and isset($response['data']['S_updateAuction']['success']) and $response['data']['S_updateAuction']['success']) {
            $message = "{$match?->game->name} için ENEBA'da stok güncelleme başarılı.Stock : $stock";

            $users = UserService::sendNotificationUsers();

            Notification::send($users, new UpdateStockNotification(MarketplaceName::ENEBA->value, $message, $game->id, true));


            Log::channel(self::successLog())->info($message);

            return true;
        } else {
            if ($updateStockRequest->unauthorized()) {
                self::refreshToken();
                $updateStockRequest = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
            }


            if ($updateStockRequest->failed()) {
                $message = "{$match?->game->name} için ENEBA'da stok güncellerken hata oluştu.";
                Log::channel(self::errorLog())->error($message);
                Log::channel(self::errorLog())->error(json_encode($updateStockRequest->json()));

                $users = UserService::sendNotificationUsers();

                Notification::send($users, new UpdateStockNotification(MarketplaceName::ENEBA->value, $message, $game->id, false));

            }
            return false;
        }

    }


    public static function getAlreadyExists(): string
    {
        return 'ALREADY_EXISTS';
    }


    public function getAllOffers()
    {
        $this->check();

        $query = '{
        S_stock {
            edges {
                node {
                    id
                    product {
                        id
                        name
                    }
                    unitsSold
                    onHold
                    onHand
                    status
                    expiresAt
                    autoRenew
                    price {
                        amount
                        currency
                    }
                    createdAt
                    priceUpdateQuota {
                        quota
                        nextFreeIn
                        totalFree
                    }
                }
            }
        }
    }';

        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        $response = $request->json();

        return $response;
    }


    public function allCallback()
    {
        $this->check();

        $query = '
                query {
                    P_apiCallbacks {
                        id
                        type
                        url
                        authorization
                    }
                }';

        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        $response = $request->json();

        return $response;
    }


    public function enebaReservationCallback($url)
    {
        $this->check();

        $token = $this->getToken();

        $query = 'mutation {
                P_registerCallback (
                    input: {
                    type: DECLARED_STOCK_RESERVATION
                    url: "' . $url . '"
                    authorization: "1b974a409fbd1fe6fa5fb430b826d2c281e7e3607d1929b1d3c173f4f29b952f"
                    }
                ) { success }
            }';

        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        $response = $request->json();

        return $response;
    }


    public function enebaProvisionCallback($url)
    {
        $this->check();

        $query = 'mutation {
                P_registerCallback (
                    input: {
                    type: DECLARED_STOCK_PROVISION
                    url: "' . $url . '"
                    authorization: "eW91ci1hdXRob3JpemF0aW9uLWhlYWRlcg=="
                    }
                ) { success }
            }';

        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        $response = $request->json();

        return $response;
    }


    public function enebaCancelledCallback($url)
    {
        $this->check();

        $query = 'mutation {
                P_registerCallback (
                    input: {
                    type: DECLARED_STOCK_CANCELLATION
                    url: "' . $url . '"
                    authorization: "eW91ci1hdXRob3JpemF0aW9uLWhlYWRlcg=="
                    }
                ) { success }
            }';

        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        $response = $request->json();

        return $response;
    }


    public function action($actionId)
    {
        $this->check();

        $query = [
            'query' => '{
                A_action(actionId: "' . $actionId . '") {
                    id
                    state
                }
            }'
        ];

        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), $query);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }


        $action = json_decode($request->body(), true);

        return $action;

    }

    public function getLowestPrice($productId): float
    {
        $this->check();

        $query = '{
        S_competition(productIds: ["' . $productId . '"]) {
            productId
            competition(first: 50) {
              totalCount
              edges {
                node {
                  belongsToYou
                  merchantName
                  price {
                    amount
                    currency
                  }
                }
              }
            }
          }
        }';


        $request = Http::withToken($this->relatedMarketPlace->token)->post(self::getGraphqlUrl(), ['query' => $query]);

        if ($request->failed()) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->get(self::getGraphqlUrl());
        }

        $response = $request->json();

        Log::channel('update_price_cron')->info("response" . json_encode($response));

        if (isset($response['data']['S_competition'][0]['competition']['edges'][0]['node']['price']['amount'])) {
            $lowPrice = ($response['data']['S_competition'][0]['competition']['edges'][0]['node']['price']['amount'] / 100) - 0.01;
            return $lowPrice;
        } else {
            $emptyPrice = 1000000.0;

            return $emptyPrice;
        }

    }

    public function getGameNameByProductId(string $productId): ?string
    {
        $response = $this->ifOfferExists($productId);

        return $response['data']['S_stock']['edges'][0]['node']['product']['name'] ?? null;
    }

    public function calculateCustomerPrice(float $price): float
    {

        return 0;
    }

    public function calculateSellerPrice(float $price): float
    {

        return 0;
    }

    public function createIfNotExistUs($productApiId, $gameId): MarketplaceMatchGame|null
    {
        $existsOffer = $this->getOffer($productApiId);

        if (!is_array($existsOffer) or !isset($existsOffer['data'])) {
            return null;
        }
        $gameData = GameService::getStockKeys($gameId);

        $upsertData = [
            'game_id' => $gameId,
            'product_id' => $productApiId,
        ];


        return MarketplaceMatchGame::updateOrCreate($upsertData, [
            'game_id' => $gameId,
            'product_id' => $productApiId,
            'offer_id' => $existsOffer['data']['S_stock']['edges'][0]['node']['id'],
            'api_game_name' => $existsOffer['data']['S_stock']['edges'][0]['node']['product']['name'] ?? null,
            'amount_us' => $gameData['amount'],
            'marketplace_id' => MarketplaceName::ENEBA->value,
            'status' => OfferStatus::ACTIVE->value,
            'amount_api' => 0,
            'who' => 77,//Eser
            'amount_currency' => CurrencyEnum::EUR->value,
        ]);
    }
}
