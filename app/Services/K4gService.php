<?php

namespace App\Services;

use App\Enums\CurrencyEnum;
use App\Enums\KeyStatus;
use App\Enums\MarketplaceName;
use App\Enums\OfferStatus;
use App\Enums\OrderTypeEnum;
use App\Enums\SellerType;
use App\Interfaces\IMarketplace;
use App\Jobs\Marketplace1\MakePassiveInMarketPlaceJob;
use App\Models\Game;
use App\Models\MarketplaceMatchGame;
use App\Models\Order;
use App\Notifications\NotifyAboutStock;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class K4gService implements IMarketplace
{
    public const WEBHOOKS = ['RESERVE', 'CANCEL', 'BUY', 'DISPATCH', 'GIVE'];

    public static function errorLog(): string
    {
        return MarketplaceName::defineErrorLog(MarketplaceName::K4G->value);
    }

    public static function successLog(): string
    {
        return MarketplaceName::defineSuccessLog(MarketplaceName::K4G->value);
    }

    public function getApiUrl(): string
    {
        return 'https://api.k4g.com/integration-api/v1/';
    }

    protected function getFixHeaders(string $signature): array
    {
        return [
            'Accept' => '*/*',
            'Content-Type' => 'application/json',
            'X-Key-Id' => config('k4g.APP_ID'),
            'X-Signature' => $signature
        ];

    }

    //Signatures
    private function getOnlyNonArrays(array $fields): array
    {
        $f = [];

        foreach ($fields as $key => $val) {
            if (is_array($val)) {
                continue;
            }
            $f[trim((string)$key)] = trim((string)$val);
        }
        return $f;
    }

    public function getToken(array $query = [], array $body = []): string
    {
        $values = array_merge($this->getOnlyNonArrays($query), $this->getOnlyNonArrays($body));

        ksort($values);

        $requestValues = [];

        array_walk($values, function ($value, $key) use (&$requestValues) {
            $requestValues[] = $key . '=' . $value;
        });

        return hash('sha256', config('k4g.APP_ID') . implode('&', $requestValues) . config('k4g.APP_SECRET'));
    }

    public function refreshToken(): void
    {

    }

    //Products
    public function searchGame(string $search): array
    {

        $query = [
            'timestamp' => time(),
            'query' => $search
        ];

        $signature = $this->getToken($query);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . 'products/search?' . $urlParam;

        $gameSearchRequest = Http::withHeaders(self::getFixHeaders($signature))->get($url);

        if (!$gameSearchRequest->ok()) {
            $message = 'K4g imza olusturma esnasında bir hata oluştu!';
            Log::channel(self::errorLog())->error($message);
            return [];
        }

        $response = json_decode($gameSearchRequest->body(), true);

        if (!empty($response['data'])) {
            $products = $response['data'];

            $data = [];

            foreach ($products as $product) {
                $data[] =
                    [
                        'id' => $product['id'],
                        'name' => $product['title']
                    ];
            }

            return $data;
        }

        $errorMessage = 'K4G serviste oyun arama esnasında istenmeyen bir şekilde cevap döndü';
        $returnResponse = json_encode($response);
        Log::channel(self::errorLog())->error($errorMessage);
        Log::channel(self::errorLog())->error("Response : " . $returnResponse);

        return [];

    }

    public function getProduct(string $productId): array
    {

        $query = [
            'timestamp' => time(),
        ];

        $signature = $this->getToken($query);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "products/get-product/$productId?$urlParam";

        $productRequest = Http::withHeaders(self::getFixHeaders($signature))->get($url);

        if (!$productRequest->ok()) {
            $message = 'K4g token alma esnasında bir hata oluştu!';
            Log::channel(self::errorLog())->error($message);
            Log::channel(self::errorLog())->error("Request :" . json_encode($productRequest));

            return [];
        }

        return json_decode($productRequest->body(), true);
    }


    //Calculations

    public function calculateSalePrice(string $offerId, int|string $price)
    {
        $query = [
            'timestamp' => time(),
            'price' => $price,
            'offerId' => $offerId
        ];

        $signature = $this->getToken($query);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "calculate/sale-price?$urlParam";

        $calculateSalePriceRequest = Http::withHeaders(self::getFixHeaders($signature))->get($url);

        $response = json_decode($calculateSalePriceRequest->body(), true);

        return $response['priceSale'];
    }

    public function calculateReceivePrice(string $offerId, int|string $price)
    {
        $query = [
            'timestamp' => time(),
            'price' => $price,
            'offerId' => $offerId
        ];

        $signature = $this->getToken($query);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "calculate/receive-price?$urlParam";

        $calculateSalePriceRequest = Http::withHeaders(self::getFixHeaders($signature))->get($url);

        $response = json_decode($calculateSalePriceRequest->body(), true);

        return $response['priceIWTR'];
    }

    //Offers
    public function offersGetPrices(string $productId): array
    {
        $query = [
            'timestamp' => time(),
            'productId' => $productId
        ];

        $signature = $this->getToken($query);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "offers/get-prices?$urlParam";

        $getOffersPricesRequest = Http::withHeaders(self::getFixHeaders($signature))->get($url);

        $response = json_decode($getOffersPricesRequest->body(), true);

        $lowest = $response['lowest'] ?? null;
        $highest = $response['highest'] ?? null;

        if ($lowest !== null && $highest !== null) {
            return [
                'lowest' => $lowest,
                'highest' => $highest
            ];
        }

        return [];
    }

    public function getOffer(string $offerId): array
    {
        $query = [
            'timestamp' => time(),
        ];

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "offers/get-offer/$offerId?$urlParam";

        $signature = $this->getToken($query);

        $offerRequest = Http::withHeaders(self::getFixHeaders($signature))->get($url);

        if ($offerRequest->notFound()) {
            $message = "Offer bulunamadı!";
            Log::channel(self::errorLog())->error($message);
            Log::channel(self::errorLog())->error("Request" . json_encode($offerRequest->body()));

            return [];
        }

        if (!$offerRequest->ok()) {
            $message = 'K4g token alma esnasında bir hata oluştu!';
            Log::channel(self::errorLog())->error($message);
            Log::channel(self::errorLog())->error("Request :" . json_encode($offerRequest->body()));

            return [];
        }

        return json_decode($offerRequest->body(), true);
    }


    public function ifOfferExists($productId): array
    {
        $product = $this->getProduct($productId);

        if (empty($product)) {
            return [];
        }

        $offer = collect($product)->get('yourOffer');

        $offerId = $offer['id'];
        $offerInfo = $this->getOffer($offerId);

        if (is_null($offerInfo)) {
            $errorMessage = 'K4g serviste var olan bir offerı almak isterken istenmeyen bir şekilde cevap döndü';
            $returnResponse = json_encode($offerInfo);
            return [];
        }

        return $offerInfo;
    }

    public function matchWithUs(array $payload, $userId = null): mixed
    {
        $productId = $payload['product_api_id'];
        $gameId = $payload['game_id'];
        $game = Game::findOrFail($gameId);

        $amount = bcmul($payload['price'], 100);

        $stockData = GameService::getStockKeys($payload['game_id']);


        $stock = $stockData['active_keys_count'];


        if ($stock == 0) {
            $message = "[$game->id]$game->name oyunun Stoğu $stock olduğu için Eşleşme Başarısız";
            Log::channel(self::errorLog())->error($message);
            return false;
        }

        $body = [
            'productId' => $productId,
            'declaredStock' => $stock,
            'duration' => 365,
            'priceIWTR' => $amount,
            'renewal' => true,
            "minimalPrice" => $amount
        ];


        $query = [
            'timestamp' => time(),
        ];

        $accessToken = $this->getToken($query, $body);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . 'offers/create?' . $urlParam;

        $request = Http::withHeaders(self::getFixHeaders($accessToken))->post($url, $body);

        $response = json_decode($request->body(), 1);


        if ($request->unauthorized()) {
            $message = 'K4g unauthorized | bir hata oluştu!';
            Log::channel(self::errorLog())->error($message);

            return [];
        }


        if ($request->conflict()) {
            //todo 409 anlamı confilict yani bu oyun önceden eşleştirilmiş burada log ve notification işlemi olacak

            $existsOffer = $this->ifOfferExists($productId);

            $existsOfferId = $existsOffer['id'];

            $this->updateOffer([
                'offer_id_api' => $existsOfferId,
                'price' => $payload['price'],
                'amount_currency' => $payload['amount_currency'] ?? CurrencyEnum::EUR->value,
                'game_id' => $payload['game_id'],
                'status' => OfferStatus::ACTIVE->value,
            ]);
            return self::getAlreadyExists();
        }

        if (!is_array($request->json()) or !(array_key_exists('product', $request->json()))) {
            $message = sprintf('K4G %s numaralı product ile bizim sistemdeki %d (%s) eşleştirilirken hata çıktı', $productId, $gameId, $game->name);
            Log::channel(MarketplaceName::defineErrorLog(MarketplaceName::K4G->value))->error($message);
            Log::channel(MarketplaceName::defineErrorLog(MarketplaceName::K4G->value))->error(json_encode($response));

            return false;
        }


        return $response;

    }

    public function changeStatus($offerIdInApi, $status, $stock = 0): bool
    {
        $status = $status == OfferStatus::ACTIVE->value ? 'ACTIVE' : 'INACTIVE';

        $match = MarketplaceMatchGame::whereOfferId($offerIdInApi)->first();
        $gameId = $match->game_id;

        $stockData = GameService::getStockKeys($gameId);

        //game'den gelir
        $price = bcmul($stockData['amount'], 100);

        $data = [
            'status' => $status,
            'declaredStock' => $stock,
            'priceSale' => $price,
            'duration' => 365,
        ];

        $query = ['timestamp' => time()];

        $accessToken = $this->getToken($query, $data);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "offers/$offerIdInApi?$urlParam";

        $offerUpdateRequest = Http::withHeaders(self::getFixHeaders($accessToken))->patch($url, $data);

        if (!$offerUpdateRequest->ok()) {
            Log::channel(self::errorLog())->error(json_encode($offerUpdateRequest->json()));
            return false;
        }

        return true;
    }

    public function updateOffer(array $payload): bool
    {
        $offerIdInK4g = $payload['offer_id_api'];

        $stockData = GameService::getStockKeys($payload['game_id']);

        //game'den gelir
        $price = bcmul($stockData['amount'], 100);

        $stock = $stockData['active_keys_count'];

        if ($stock == 0) {
            $message = "[{$stockData['id']} {$stockData['name']} oyunun Stoğu $stock olduğu için güncelleme Başarısız";
            Log::channel(self::errorLog())->error($message);

            return false;
        }

        $data = [
            'declaredStock' => $stock,
            'duration' => 365,
            'priceIWTR' => $price,
            'status' => $payload['status'] == OfferStatus::ACTIVE->value ? 'ACTIVE' : 'INACTIVE',
            'renewal' => true,
            'minimalPrice' => $price
        ];

        $query = ['timestamp' => time(),];

        $accessToken = $this->getToken($query, $data);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "offers/$offerIdInK4g?$urlParam";

        $offerUpdateRequest = Http::withHeaders(self::getFixHeaders($accessToken))->patch($url, $data);

        if (!$offerUpdateRequest->ok()) {
            Log::channel(self::errorLog())->error(json_encode($offerUpdateRequest->json()));
            return false;
        }

        $message = "{$stockData['name']} için K4G'de stok güncelleme başarılı.Stock : $stock";
        Log::channel(self::successLog())->info($message);

        return true;
    }

    public function updateStock($offerId, $gameId): bool
    {

        $stockData = GameService::getStockKeys($gameId);

        $stock = $stockData['active_keys_count'];

        GameService::notifyUserAboutStock($stock, $stockData, $gameId, MarketplaceName::K4G);

        dispatch(new MakePassiveInMarketPlaceJob($gameId, $stock, MarketplaceName::K4G->value));

        $query = ['timestamp' => time(),];
        $price = bcmul($stockData['amount'], 100);

        $data = ['declaredStock' => $stock, 'priceIWTR' => $price];

        $accessToken = $this->getToken($query, $data);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "offers/$offerId?$urlParam";

        $offerUpdateRequest = Http::withHeaders(self::getFixHeaders($accessToken))->patch($url, $data);

        if (!$offerUpdateRequest->ok()) {
            Log::channel(self::errorLog())->error(json_encode($offerUpdateRequest->json()));
            return false;
        }

        return true;
    }

    public static function getAlreadyExists(): string
    {
        return 'ALREADY_EXISTS';
    }

    public function getLowestPrice(int|string $productId): float
    {
        $prices = $this->offersGetPrices($productId);

        if (isset($prices['lowest'])) {
            return $prices['lowest'];
        }

        return 1000000.0;
    }

    public function getGameNameByProductId(string $productId): ?string
    {
        $response = $this->getProduct($productId);


        if (is_array($response) && isset($response['title'])) {
            return $response['title'];
        }

        return null;
    }

    public function deleteOffer($offerIdInGamivo)
    {
        $url = self::getApiUrl() . '';

        return 'böyle bir durum yok';

    }


    //Webhooks
    public function listWebhooks(): array
    {
        $query = [
            'timestamp' => time(),
        ];

        $signature = $this->getToken($query);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "webhooks?$urlParam";

        $gameSearchRequest = Http::withHeaders(self::getFixHeaders($signature))->get($url);

        if (!$gameSearchRequest->ok()) {
            $message = 'K4g imza olusturma esnasında bir hata oluştu!';
            Log::channel(self::errorLog())->error($message);
            return [];
        }

        return json_decode($gameSearchRequest->body(), true);
    }

    public function getWebhook(string $webhookId): array
    {
        $query = ['timestamp' => time(),];

        $signature = $this->getToken($query);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "webhooks/$webhookId?$urlParam";

        $webhookRequest = Http::withHeaders(self::getFixHeaders($signature))->get($url);

        if (!$webhookRequest->ok()) {
            $message = 'K4g imza olusturma esnasında bir hata oluştu!';
            Log::channel(self::errorLog())->error($message);
            Log::channel(self::errorLog())->error("Request :" . json_encode($webhookRequest));

            return [];
        }

        return json_decode($webhookRequest->body(), true);
    }

    public function deleteWebHook(string $webhookId): bool
    {
        $query = ['timestamp' => time(),];

        $signature = $this->getToken($query);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "webhooks/$webhookId?$urlParam";

        $webhookRequest = Http::withHeaders(self::getFixHeaders($signature))->delete($url);

        if (!$webhookRequest->ok()) {
            $message = 'K4g imza olusturma esnasında bir hata oluştu!';
            Log::channel(self::errorLog())->error($message);
            Log::channel(self::errorLog())->error("Request :" . json_encode($webhookRequest));

            return false;
        }

        return true;
    }

    public function createWebhook(string $url, string $type): bool
    {

        $list = $this->listWebhooks();


        $query = ['timestamp' => time()];

        $data = [
            "url" => $url,
            "type" => $type
        ];

        $signature = $this->getToken($query, $data);

        $urlParam = http_build_query($query);

        $requestUrl = self::getApiUrl() . "webhooks/create?$urlParam";

        $createWebhookRequest = Http::withHeaders(self::getFixHeaders($signature))->post($requestUrl, $data);

        $res = $createWebhookRequest->json();
        if (!isset($res['id'])) {
            $message = 'K4g imza olusturma esnasında bir hata oluştu!';
            Log::channel(self::errorLog())->error($message);
            Log::channel(self::errorLog())->error("Request :" . json_encode($createWebhookRequest));

            return false;
        }

        return true;
    }

    public function giveKeyToK4G($offerId, $body): bool
    {
        //44D54754

        $query = ['timestamp' => time()];


        $accessToken = $this->getToken($query, $body);

        $urlParam = http_build_query($query);

        $url = self::getApiUrl() . "offers/$offerId/stock/list?$urlParam";

        $loadKeyRequest = Http::withHeaders(self::getFixHeaders($accessToken))->post($url, $body);

        if (!($loadKeyRequest->successful())) {
            Log::channel(self::errorLog())->info('Key karışıa verlirken hata oluştu');
            Log::channel(self::errorLog())->info(json_encode($loadKeyRequest->json()));

            return false;
        }

        return true;


    }

    public function takeKeysAsBodyWithOrderId($orderId = null): array
    {
        $orderId = $orderId ?? 150230;

        $order = Order::with('keys:id,key,order_id')->findOrFail($orderId);
        $body = [];


        foreach ($order->keys as $key) {
            $body [] = [
                "type" => "TEXT",
                "body" => $key->key,
//                "body" => 'xxxxxx',
                "reservationId" => $order->reservation_id
            ];
        }

        return $body;
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
            'api_game_name' => $existsOffer['product']['title'] ?? null,
            'amount_us' => $gameData['amount'],
            'marketplace_id' => MarketplaceName::K4G->value,
            'status' => OfferStatus::ACTIVE->value,
            'amount_api' => 0,
            'who' => 77,//Eser
            'amount_currency' => CurrencyEnum::EUR->value,
        ]);
    }

}




