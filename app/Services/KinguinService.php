<?php

namespace App\Services;

use App\Enums\CurrencyEnum;
use App\Enums\KeyStatus;
use App\Enums\MarketplaceName;
use App\Enums\OfferStatus;
use App\Enums\RoleEnum;
use App\Interfaces\IMarketplace;
use App\Jobs\Marketplace1\MakePassiveInMarketPlaceJob;
use App\Models\Currency;
use App\Models\Game;
use App\Models\MarketPlace;
use App\Models\MarketplaceMatchGame;
use App\Models\User;
use App\Notifications\UpdateStockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class KinguinService implements IMarketplace
{

    public MarketPlace $relatedMarketPlace;

    public const MAX_STOCK = 500;

    private function check(): bool
    {

        $relatedMarketPlace = MarketPlace::findByName(MarketplaceName::KINGUIN->name)->first();


        try {
            if (!$relatedMarketPlace) {
                //todo burada marketplace yoksa log ve Notification yollansın gerekli kişilere yani bize
                $developers = User::role(RoleEnum::BACKEND_DEVELOPER->value)->get();
                $message = 'Eneba serviste ilişkili MarketPlace modeli bulunmadığı için hata!';

                return false;
            }
        } catch (\Exception $exception) {
            Log::channel(self::errorLog())->error($exception->getMessage());
            return false;
        }

        $token = $relatedMarketPlace->token;


        if (is_null($token)) {
            $token = self::getToken();
            $relatedMarketPlace->update(['token' => $token]);
        }

        $this->relatedMarketPlace = MarketPlace::findByName(MarketplaceName::KINGUIN->name)->first();

        return true;
    }


    public function getReservationByReservationId($reservationId, $item): ?array
    {
        $reservationId = $item['reservation_id'];


        $myData = DB::connection('b2b_live')->table('reservation_contents')->where('reservation_id', $reservationId)->first();
        $reservation = DB::connection('b2b_live')->table('kinguin_reserves')->where('reserve_id', $reservationId)->first();
        $reserveId = $reservation?->reserve_id_in_api;


        $d = DB::connection('b2b_live')->table('marketplace_api_games_match')->where('game_id', $myData->game_id)->where('api_id', 4)->get();


        if ($d->count() == 1) {
            return [$d[0]->game_id_in_api, $reserveId];
        } else {
            Log::error($item['order_code'] . ' =>>' . 'bunda match için birden fzla bulundu');
        }

        return null;
    }

    public static function errorLog(): string
    {
        return MarketplaceName::KINGUIN->name . '_error';
    }

    public static function successLog(): string
    {
        return MarketplaceName::KINGUIN->name . '_success';
    }

    public function getApiUrl(): string
    {
        return 'https://gateway.kinguin.net/sales-manager-api/api/v1/';
    }

    public function getToken(): mixed
    {
        $req = Http::asForm()->post('https://id.kinguin.net/oauth/v2/token', [
            'grant_type' => 'password',
            'client_id' => config('kinguin.CLIENT_ID'),
            'client_secret' => config('kinguin.CLIENT_SECRET'),
            'username' => config('kinguin.USERNAME'),
            'password' => config('kinguin.PASSWORD'),
        ]);

        if ($req->status() != 200) {
            //todo log başarısız ise token alma işlemi

            $message = 'Kinguinde token alma esnasında bir hata oluştu!';

            return null;
        }

        $data = json_decode($req->body(), 1);

        return $data['access_token'];
    }

    public function refreshToken(): void
    {
        $this->check();

        $token = self::getToken();
        $this->relatedMarketPlace->update(['token' => $token]);
    }

    public function searchGame($search): array
    {
        $this->check();

        $search = trim($search);
        $url = "https://gateway.kinguin.net/library/api/v1/products/search?active=1&phrase={$search}";

        $gameSearchRequest = Http::get($url);

        if ($gameSearchRequest->status() != 200) {
            $message = 'Kinguinde token alma esnasında bir hata oluştu!';

            return [];
        }

        $response = json_decode($gameSearchRequest->body(), 1);

        if (isset($response['_embedded']['products']) && is_array($response['_embedded']['products']) && !empty($response['_embedded']['products'])) {
            $products = $response['_embedded']['products'];
            $data = [];
            foreach ($products as $product) {
                $data[] = ['id' => $product['id'], 'name' => $product['name']];
            }

            return $data;
        }
        //todo log başarısız oldu
        $errorMessage = 'Kingunserviste oyun arama esnasında istenmeyen bir şekilde cevap döndü';
        $returnResponse = json_encode($response);

        return [];

    }

    public function ifOfferExists($productId): array
    {
        $this->check();

        $url = self::getApiUrl() . "offers?productId={$productId}";

        $request = Http::withToken($this->relatedMarketPlace->token)->get($url);


        if ($request->status() == 401) {
            self::refreshToken();
            $response = Http::withToken($this->relatedMarketPlace->token)->get($url);
        }

        $response = $request->json();

        if ($request->status() == 200
            && isset($response['_embedded'])
            && isset($response['_embedded']['offerList'])
            && is_array($response['_embedded']['offerList'])
            && !empty($response['_embedded']['offerList'])
        ) {
            // TODO başarılı ise alma işlemi
            return $response['_embedded']['offerList'][0];
        }

        $errorMessage = 'Kingun serviste var olan bir offerı almak isterken istenmeyen bir şekilde cevap döndü';
        $returnResponse = json_encode($response);

        return [];
        //todo alama işlemi başarısız ise burayı loglarla destekleyeceğiz takip için
    }

    public function matchWithUs(array $payload, $userId = null): mixed
    {
        $this->check();

        $url = self::getApiUrl() . 'offers';

        $stockData = GameService::getStockKeys($payload['game_id']);
        $stock = $stockData['active_keys_count'];
        $productId = $payload['product_api_id'];
        $declaredStock = min($stock, self::MAX_STOCK);
        $amount = is_string($payload['price']) ? PriceService::convertStrToFloat($payload['price']) : $payload['price'];
        $amount = $amount * 100;
        $dbCurrency = Currency::findOrFail($payload['amount_currency'] ?? CurrencyEnum::EUR->value);
        $currency = $dbCurrency->name;


        $data = [
            'productId' => $productId,
            'declaredStock' => $declaredStock,
            'price' => [
                'amount' => $amount,
                'currency' => $currency,
            ],
            'status' => KeyStatus::ACTIVE->name,
        ];

        $request = Http::withToken($this->relatedMarketPlace->token)->post($url, $data);

        if ($request->status() == 401) {
            self::refreshToken();
            $request = Http::withToken($this->relatedMarketPlace->token)->post($url, $data);
        }

        if ($request->status() == 409) {
            //todo 409 anlamı confilict yani bu oyun önceden eşleştirilmiş burada log ve notification işlemi olacak

            $existsOffer = $this->ifOfferExists($payload['product_api_id']);
            $existsOfferId = $existsOffer['id'];

            $this->updateOffer([
                'offer_id_api' => $existsOfferId,
                'price' => $payload['price'],
                'amount_currency' => $payload['amount_currency'] ?? CurrencyEnum::EUR->value,
                'game_id' => $payload['game_id'],
                'status' => OfferStatus::ACTIVE->value,
            ]);

            //eşleşme esansında ne yapılacak sorulacak
            return self::getAlreadyExists();
        }

        $response = $request->json();
        $gameId = $payload['game_id'];
        $game = Game::findOrFail($gameId);

        //Log::channel(MarketplaceName::defineErrorLog(MarketplaceName::KINGUIN->value))->error('mesajjjjjjjjj');
        //Log::channel(MarketplaceName::defineErrorLog(MarketplaceName::KINGUIN->value))->error(json_encode($response));
        if ($request->status() != 200 || !isset($response['id'])
        ) {

            $message = sprintf('Kinguindeki %s numaralı product ile bizim sistemdeki %d (%s) eşleştirilirken hata çıktı', $productId, $gameId, $game->name);
            Log::channel(MarketplaceName::defineErrorLog(MarketplaceName::KINGUIN->value))->error($message);
            Log::channel(MarketplaceName::defineErrorLog(MarketplaceName::KINGUIN->value))->error(json_encode($response));

            return false;
        }

        return $response;
    }

    public function changeStatus($offerIdInApi, $status, $stock = 0): bool
    {
        $this->check();


        $url = self::getApiUrl() . "offers/{$offerIdInApi}";

        $status = $status == OfferStatus::ACTIVE->value ? 'ACTIVE' : 'INACTIVE';

        $stock = min($stock, self::MAX_STOCK);

        $match = MarketplaceMatchGame::with('game')->whereOfferId($offerIdInApi)->first();

        $amount = $match->game->amount;

        $amount = $amount * 100;

        $inactiveRequest = Http::withToken($this->relatedMarketPlace->token)->patch($url, [
            'status' => $status,
            'declaredStock' => $stock,
            'price' => [
                'amount' => $amount,
                'currency' => CurrencyEnum::EUR->name,
            ],
        ]);

        if ($inactiveRequest->status() == 401) {
            self::refreshToken();
            $inactiveRequest = Http::withToken($this->relatedMarketPlace->token)->patch($url, [
                'status' => $status,
                'declaredStock' => 0,
            ]);
        }

        if ($inactiveRequest->status() != 200) {
            $errorMessage = "Eşleşmeyi $status yaparken bir hata oluştu";
            $returnResponse = json_encode($inactiveRequest->json());
            Log::channel(self::errorLog())->error($errorMessage);
            Log::channel(self::errorLog())->error($returnResponse);
            return false;
        }

        return true;
    }

    public function updateOffer(array $payload): bool
    {

        $this->check();

        $offerIdInKinguin = $payload['offer_id_api'];
        $url = self::getApiUrl() . "offers/{$offerIdInKinguin}";

        $stockData = GameService::getStockKeys($payload['game_id']);

        //gameden gelir
        $payload['price'] = $stockData['amount'];

        $amount = is_string($payload['price']) ? PriceService::convertStrToFloat($payload['price']) : $payload['price'];
        $amount = $amount * 100;
        //todo api satışları hep eur mu diye sor emin ol burayı değiştir zorunlu kıl
        $dbCurrency = Currency::findOrFail($payload['amount_currency'] ?? CurrencyEnum::EUR->value);
        $currency = $dbCurrency->name;


        $stock = $stockData['active_keys_count'];

        $stock = min($stock, self::MAX_STOCK);

        $data = [
            'status' => $payload['status'] == OfferStatus::ACTIVE->value ? 'ACTIVE' : 'INACTIVE',
            'declaredStock' => $stock,
            'price' => [
                'amount' => $amount,
                'currency' => $currency,
            ],
        ];
        $updateRequest = Http::withToken($this->relatedMarketPlace->token)->patch($url, $data);


        if ($updateRequest->status() == 401) {
            self::refreshToken();
            $updateRequest = Http::withToken($this->relatedMarketPlace->token)->patch($url, $data);
        }


        if ($updateRequest->status() != 200) {
            Log::channel(self::errorLog())->error(json_encode($updateRequest->json()));
            return false;
        }
        $game = Game::findOrFail($payload['game_id']);
        $message = "{$game->name} için KINGUIN'de stok güncelleme başarılı.Stock : $stock";

        Log::channel(self::successLog())->info($message);

//        $users = UserService::sendNotificationUsers();
//
//        Notification::send($users, new UpdateStockNotification(MarketplaceName::KINGUIN->value, $message, $game->id, true));
        return true;

    }

    public function updateStock($offerId, $gameId): bool
    {
        $this->check();

        $url = self::getApiUrl() . "offers/$offerId";

        $stockData = GameService::getStockKeys($gameId);

        $amount = is_string($stockData['amount']) ? PriceService::convertStrToFloat($stockData['amount']) : $stockData['amount'];

        $amount = $amount * 100;

        $stock = min($stockData['active_keys_count'], self::MAX_STOCK);

        GameService::notifyUserAboutStock($stock, $stockData, $gameId, MarketplaceName::KINGUIN);

        dispatch(new MakePassiveInMarketPlaceJob($gameId, $stock, MarketplaceName::KINGUIN->value));


        $updateStockRequest = Http::withToken($this->relatedMarketPlace->token)->put($url, ['declaredStock' => $stock, 'price' => [
            'amount' => $amount,
            'currency' => CurrencyEnum::EUR->name,
        ],]);


        if ($updateStockRequest->status() == 401) {
            self::refreshToken();
            $updateStockRequest = Http::withToken($this->relatedMarketPlace->token)->put($url, ['declaredStock' => $stock, 'price' => [
                'amount' => $amount,
                'currency' => CurrencyEnum::EUR->name,
            ],]);
        }

//        $match = MarketplaceMatchGame::where('offer_  id', $offerId)->with('game')->firstOrFail();
        $game = Game::findOrFail($gameId);

        if ($updateStockRequest->failed()) {

            $message = "{$game->name} için Kıngıinde stok güncellerken hata oluştu( $offerId )";
            Log::channel(self::errorLog())->error($message);
            Log::channel(self::errorLog())->error(json_encode($updateStockRequest->json()));
            return false;
        }

        $message = "{$game->name} için Kıngıinde stok güncelleme başarılı.Stock : $stock";
        Log::channel(self::successLog())->info($message);

        $users = UserService::sendNotificationUsers();

        Notification::send($users, new UpdateStockNotification(MarketplaceName::KINGUIN->value, $message, $game->id, false));

        return true;
    }

    public static function getAlreadyExists(): string
    {
        return 'ALREADY_EXISTS';
    }

    public function deleteOffer($offerIdInKinguin)
    {
        $this->check();

        $url = 'https://gateway.kinguin.net/sales-manager-api/api/v1/offers/' . $offerIdInKinguin;

        $deleteRequest = Http::withToken($this->relatedMarketPlace->token)->delete($url);

        if ($deleteRequest->status() == 401) {
            self::refreshToken();
            $deleteRequest = Http::withToken($this->relatedMarketPlace->token)->delete($url);
        }

        if ($deleteRequest->status() == 204) {
            return true;
        }

        return false;
    }

    public function giveKeyToKinguin(string $offerId, string $key): bool
    {

        $checkResult = $this->check();

        if (!$checkResult) {
            return $checkResult;
        }

        $url = self::getApiUrl() . 'offers/' . $offerId . '/stock';

        $giveKeyRequest = Http::withToken($this->relatedMarketPlace->token)->post($url, [
            'body' => $key,
            'mimeType' => 'text/plain'
        ]);

        if ($giveKeyRequest->status() == 401) {

            self::refreshToken();
            $giveKeyRequest = Http::withToken($this->relatedMarketPlace->token)->post($url, [
                'body' => $key,
                'mimeType' => 'text/plain'
            ]);
        }


        if ($giveKeyRequest->failed()) {
            return false;
        }
        return true;


    }

    public function getLowestPrice(int|string $productId): float
    {
        $this->check();

        $url = self::getApiUrl() . "wholesale/product/{$productId}/offers";

        $request = Http::withToken($this->relatedMarketPlace->token)->get($url);

        if ($request->status() == 401) {
            self::refreshToken();
            $response = Http::withToken($this->relatedMarketPlace->token)->get($url);
        }

        $offers = $request->json();

        if ($request->status() == 200) {

            $lowPrice = (collect($offers)->pluck('price.amount')->min() / 100) - 0.01;
            return $lowPrice;
        }

        return 1000000.0;
    }

    public function getGameNameByProductId(string $productId): ?string
    {
        $response = $this->ifOfferExists($productId);

        if (isset($response['name'])) {
            return $response['name'];
        }
        return null;
    }

    public function calculateCustomerPrice(float $price): float
    {
        $fee = 0.15;
        $commissionPercent = 11;
        $calculatePercent = $commissionPercent / 100;
        return PriceService::convertFloat($price + $fee + +($price * $calculatePercent));

    }

    public function calculateSellerPrice(float $price): float
    {

        $x = 0.15;
        $y = 11 / 100;
        $a = $price;

        return PriceService::convertFloat(($a - $x) / (1 + $y));
    }

    public function createIfNotExistUs($productApiId, $gameId): MarketplaceMatchGame|null
    {
        $existsOffer = $this->ifOfferExists($productApiId);


        if (is_array($existsOffer) and !isset($existsOffer['id'])) {
            $existsOffer = $this->ifOfferExists($productApiId);
        }


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
            'api_game_name' => $existsOffer['name'] ?? null,
            'amount_api' => 0,
            'amount_us' => $gameData['amount'],
            'marketplace_id' => MarketplaceName::KINGUIN->value,
            'status' => OfferStatus::ACTIVE->value,
            'who' => 77,//Eser
            'amount_currency' => CurrencyEnum::EUR->value,
        ]);

    }

}
