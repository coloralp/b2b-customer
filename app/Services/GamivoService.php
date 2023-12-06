<?php

namespace App\Services;

use App\Enums\CurrencyEnum;
use App\Enums\MarketplaceName;
use App\Enums\OfferStatus;
use App\Interfaces\IMarketplace;
use App\Jobs\Marketplace1\MakePassiveInMarketPlaceJob;
use App\Models\Game;
use App\Models\MarketPlace;
use App\Models\MarketplaceMatchGame;
use App\Notifications\UpdateStockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class GamivoService implements IMarketplace
{

    public const SELLER_NAME = 'SocratGames'; //bu gamivo apide seller_name'miz

    public MarketPlace $relatedMarketplace;

    public string $accessToken;


    private function check()
    {
        $this->relatedMarketplace = MarketPlace::where('name', MarketplaceName::GAMIVO->name)->firstOrFail();
        $this->accessToken = $this->relatedMarketplace?->token ?? config('gamivo.GAMIVO_ACCESS_TOKEN');

        if (!$this->accessToken) {
            Log::channel(GamivoService::errorLog())->error('Gamivo için access token null geldi');
        }
    }

    public static function errorLog(): string
    {
        return MarketplaceName::GAMIVO->name . '_error';
    }

    public static function successLog(): string
    {
        return MarketplaceName::GAMIVO->name . '_success';
    }

    public function getApiUrl(): string
    {
        return 'https://backend.gamivo.com';
    }

    public function getToken(): mixed
    {
        return MarketPlace::where('name', MarketplaceName::GAMIVO->name)->firstOrFail()?->token ?? (config('gamivo.FROM_GAMIVO_ACCESS_TOKEN'));
    }

    public function refreshToken(): void
    {

        $this->accessToken = self::getToken();
    }

    public function searchGame1(string $search): array
    {
        $this->check();

        $search = trim($search);
        
        $searchGameRequest = Http::withToken($this->accessToken)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->get('https://backend.gamivo.com/api/public/v1/products/list-by-criteria/0/100', [
                'filters' => json_encode([
                    'name' => $search,
                    'is_preorder' => false,
                ]),
            ]);


        if ($searchGameRequest->failed()) {
            Log::channel(self::errorLog())->error('oyun arama esnasında sorun oluştu');
            Log::channel(self::errorLog())->error(json_encode($searchGameRequest->json()));

            return [];
        }


        $products = $searchGameRequest->json();

        $result = [];

        foreach ($products as $product) {
            $result [] = [
                'id' => $product['id'],
                'name' => $product['name'],
            ];
        }

        return $result;
    }

    public function searchGame(string $search): array
    {
        $this->check();

        $search = trim($search);

        $url = self::getApiUrl() . "/api/public/v1/products/by-slug/$search";

        $searchGameRequest = Http::withToken($this->accessToken)->get($url);


        if ($searchGameRequest->failed()) {
            Log::channel(self::errorLog())->error('oyun arama esnasında sorun oluştu');
            Log::channel(self::errorLog())->error(json_encode($searchGameRequest->json()));

            return [];
        }

        $products = json_decode($searchGameRequest);
        $result = [];
        if (!isset($products->codeMessage)) {
            $result[] = [
                "id" => $products->id,
                "name" => $products->name,
            ];
        }

        return $result;
    }

    public function ifOfferExists($productId): array
    {
        $this->check();

        //gamivoda offer aktif değilse [] döner
        $url = self::getApiUrl() . "/api/public/v1/products/{$productId}/offers";

        $checkExistsOfferRequest = Http::withToken($this->accessToken)->get($url);

        if ($checkExistsOfferRequest->failed()) {
            Log::channel(self::errorLog())->error('Önceden offer var mı yok mu kontrol edilirken sorun oluştu');
            Log::channel(self::errorLog())->error(json_encode($checkExistsOfferRequest->json()));

            return [];
        }

        $offers = $checkExistsOfferRequest->json();


        if (!is_array($offers)) {
            return [];
        }

        $myOffers = collect($offers)->where('seller_name', self::SELLER_NAME)->toArray();

        return empty($myOffers) ? [] : end($myOffers);
    }

    public function getOfferFromApi($offerId)
    {
        $this->check();
        $req = Http::withToken($this->accessToken)->get(self::getApiUrl() . "/api/public/v1/offers/$offerId");

        if ($req->failed()) {
            Log::channel(self::errorLog())->error('Önceden offer var mı yok mu kontrol edilirken sorun oluştu');
            Log::channel(self::errorLog())->error(json_encode($req->json()));

            return [];
        }
        return $req->json();
    }

    public function matchWithUs(array $payload, $userId = null): mixed
    {
        $this->check();

        $url = self::getApiUrl() . '/api/public/v1/offers';

        $gameId = $payload['game_id'];
        $game = Game::withCount('activeKeys')->firstOrFail($gameId);

        unset($payload['game_id']);


        $stockData = GameService::getStockKeys($gameId);
        $payload['keys'] = $stockData['active_keys_count'];

        $createOfferRequest = Http::withToken($this->accessToken)->post($url, $payload);

        if ($createOfferRequest->failed()) {

            $errResponse = $createOfferRequest->json();

            if (is_array($errResponse) && isset($errResponse['reason']) && str_contains($errResponse['reason'], 'already')) {
                $startTag = '[';
                $endTag = ']';
                $string = $errResponse['reason'];
                $startPos = strpos($string, $startTag);
                $endPos = strpos($string, $endTag);
                $existsOfferId = substr($string, $startPos + strlen($startTag), $endPos - $startPos - strlen($startTag));

                $this->changeStatus($existsOfferId, 1, $game->stock);

                $this->updateOffer([
                    'offer_id_api' => $existsOfferId,
                    'price' => $payload['seller_price'],
                    'status' => $payload['status'] ?? OfferStatus::ACTIVE->value,
                    'game_id' => $gameId
                ]);


                return [self::getAlreadyExists(), $existsOfferId];
            }

            Log::channel(self::errorLog())->error('Eşleştirme yapılırken hata oluştu');
            Log::channel(self::errorLog())->error(json_encode($payload));
            Log::channel(self::errorLog())->error(json_encode($createOfferRequest->json()));
            return false;

        }


        return $createOfferRequest->json(); //eğer doğru ise offerId yi numeric olarak döner integer olarak
    }


    public function changeStatus($offerIdInApi, $status, $stock = 0): bool
    {
        $this->check();

        $url = self::getApiUrl() . "/api/public/v1/offers/{$offerIdInApi}/change-status";

        $changeStatusRequest = Http::withToken($this->accessToken)->put($url, [
            'status' => $status == OfferStatus::ACTIVE->value ? 1 : 0,
        ]);


        if (($changeStatusRequest->json()) !== (int)$offerIdInApi) {
            $errorMessage = "Eşleşmeyi {$status} yaparken bir hata oluştu";
            $returnResponse = json_encode($changeStatusRequest->json());
            Log::channel(self::errorLog())->error($errorMessage);
            Log::channel(self::errorLog())->error($returnResponse);
            return false;
        }


        return true;

    }


    public function updateOffer(array $payload): bool
    {
        $this->check();

        $url = self::getApiUrl() . "/api/public/v1/offers/{$payload['offer_id_api']}";

        $stockData = GameService::getStockKeys($payload['game_id']);
        $stock = $stockData['active_keys_count'];

        //gameden gelir
        $price = $stockData['amount'];

        $data = [
            "wholesale_mode" => $payload['wholesale_mode'] ?? 0,
            // "seller_price" => $payload['price'],
            "seller_price" => $price,
            "tier_one_seller_price" => $payload['tier_one_seller_price'] ?? 0,
            "tier_two_seller_price" => $payload['tier_two_seller_price'] ?? 0,
            "status" => $payload['status'] == OfferStatus::ACTIVE->value ? 1 : 0,
            "keys" => $stock,
//            "keys" => 1,
            "is_preorder" => $payload['is_preorder'] ?? 0
        ];

        Log::channel(self::errorLog())->error('update payload');
        Log::channel(self::errorLog())->error(json_encode($data));
        Log::channel(self::errorLog())->error($url);

        $updateOfferRequest = Http::withToken($this->accessToken)->put($url, $data);

        if ($updateOfferRequest->failed()) {
            $game = Game::findOrFail($payload['game_id']);
            $message = "Gamivo'da {$game->name} güncellenmeye çalışırken hata oluştu";

            Log::channel(self::errorLog())->error($message);
            Log::channel(self::errorLog())->error(json_encode($updateOfferRequest));
            return false;
        }


        return true;
    }

    public function updateStock($offerId, $gameId): bool
    {
        $this->check();

        $url = self::getApiUrl() . "/api/public/v1/offers/$offerId";
        $stockData = GameService::getStockKeys($gameId);
        $stock = $stockData['active_keys_count'];

        GameService::notifyUserAboutStock($stock, $stockData, $gameId, MarketplaceName::GAMIVO);

        dispatch(new MakePassiveInMarketPlaceJob($gameId, $stock, MarketplaceName::GAMIVO->value));

        $getOfferUrl = self::getApiUrl() . "/api/public/v1/offers/$offerId";

        $offerInApi = Http::withToken($this->accessToken)->get($getOfferUrl);

        if ($offerInApi->failed()) {
            return false;
        }

        $offer = $offerInApi->json();

        $data = [
            "wholesale_mode" => $offer['wholesale_mode'] ?? 0,
//            "seller_price" => $offer['seller_price'],
            "seller_price" => $stockData['amount'],
            "tier_one_seller_price" => $offer['wholesale_price_tier_one'] ?? 0,
            "tier_two_seller_price" => $offer['wholesale_price_tier_two'] ?? 0,
            "status" => $offer['status'],
            "is_preorder" => $offer['is_preorder'],
            "keys" => $stock,
        ];

        $updateOfferRequest = Http::withToken($this->accessToken)->put($url, $data);


        $game = Game::findOrFail($gameId);

        if ($updateOfferRequest->failed()) {

            $message = "{$game?->name} için Gamivo'da stok güncellerken hata oluştu.";
            Log::channel(self::errorLog())->error($message);
            return false;
        }

        $message = "{$game?->name} için Gamivo'da stok güncelleme başarılı Stock : $stock ";

        Log::channel(self::successLog())->info($message);


        $users = UserService::sendNotificationUsers();

        Notification::send($users, new UpdateStockNotification(MarketplaceName::GAMIVO->value, $message, $gameId, false));

        return true;
    }

    public static function getAlreadyExists(): string
    {
        return 'ALREADY_EXISTS';
    }

    public function deleteOffer($offerIdInGamivo)
    {
        $this->check();

        $url = self::getApiUrl() . '';

        return 'böyle bir durum yok';

    }

    public function getOffer($item)
    {

        $reservationId = $item['reservation_id'];


        $myData = DB::connection('b2b_live')->table('reservation_contents')->where('reservation_id', $reservationId)->first();

        $d = DB::connection('b2b_live')->table('marketplace_api_games_match')->where('game_id', $myData->game_id)->where('api_id', 2)->get();

        if ($d->count() == 1) {
            return [$d[0]->game_id_in_api, $myData?->reservation_id];
        } else {

            Log::error($item['order_code'] . ' =>>' . 'bunda match için birden fzla bulundu');

        }

    }

    public function getAllOffers()
    {
        $this->check();


        $offers = MarketplaceMatchGame::whereMarketplaceId(MarketplaceName::GAMIVO->value)->get();
        foreach ($offers as $offer) {
            $this->changeStatus($offer->offer_id, 0);
        }
    }

    public function getLowestPrice(int|string $productId): float
    {
        $this->check();

        $url = self::getApiUrl() . "/api/public/v1/products/{$productId}";

        $productInfoRequest = Http::withToken($this->accessToken)->get($url);

        if ($productInfoRequest->failed()) {
            return 1000000.0;
        }

        $productInfo = json_decode($productInfoRequest);

        $lowPrice = collect($productInfo)->get('lowest_price') - 0.01;

        if (!$lowPrice or $lowPrice == 0) {
            return 1000000.0;
        }

        return $lowPrice;
    }

    public function getGameNameByProductId(string $productId): ?string
    {
        $this->check();
        $url = self::getApiUrl() . "/api/public/v1/products/$productId";

        $request = Http::withToken($this->accessToken)->get($url);

        if ($request->successful()) {
            $response = $request->json();

            if (is_array($response) and isset($response['name'])) {
                return $response['name'];
            }

            return null;
        }
        return null;
    }

    public function calculateCustomerPrice($offerId, float $wantPrice): ?float
    {

        $this->check();

        $data = [
            'seller_price' => $wantPrice
        ];

        $query = http_build_query($data);
        $url = self::getApiUrl() . "/api/public/v1/offers/calculate-customer-price/$offerId?$query";

        $request = Http::withToken($this->accessToken)->get($url);

        $response = $request->json();

        if ($request->failed()) {
            return null;
        }

        return $response['customer_price'];
    }

    public function calculateSellerPrice($offerId, float $customerPrice): ?float
    {

        $this->check();

        $data = [
            'price' => $customerPrice
        ];

        $query = http_build_query($data);
        $url = self::getApiUrl() . "/api/public/v1/offers/calculate-seller-price/$offerId?$query";

        $request = Http::withToken($this->accessToken)->get($url);

        $response = $request->json();

        if ($request->failed()) {
            return null;
        }

        return $response['seller_price'];
    }

    public function createIfNotExistUs($productApiId, $gameId): MarketplaceMatchGame|null
    {
        $existsOffer = $this->ifOfferExists($productApiId);


        if (!is_array($existsOffer)) {
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
            'offer_id' => $existsOffer['id'],
            'api_game_name' => $existsOffer['product_name'] ?? null,
            'amount_us' => $gameData['amount'],
            'marketplace_id' => MarketplaceName::KINGUIN->value,
            'status' => OfferStatus::ACTIVE->value,
            'amount_api' => $existsOffer['retail_price'] ?? 0,
            'who' => 77,//Eser
            'amount_currency' => CurrencyEnum::EUR->value,
        ]);
    }
}
