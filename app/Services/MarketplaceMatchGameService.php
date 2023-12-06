<?php

namespace App\Services;

use App\Enums\KeyStatus;
use App\Enums\MarketplaceName;
use App\Http\Resources\Api\MarketplaceMach\History\OfferUpdeteHistoryListResource;
use App\Models\ApiOfferPriceUpdate;
use App\Models\CommonSummary;
use App\Models\Game;
use App\Models\GameStockUpdate;
use App\Models\Key;
use App\Models\MarketplaceMatchGame;
use Illuminate\Database\Eloquent\Builder;

class MarketplaceMatchGameService
{
    public function lastUpdates(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $lastTenUpdates = ApiOfferPriceUpdate::with(['match' => function ($query) {
            $query->select(['id', 'game_id', 'marketplace_id'])->with(['game:id,uuid,name']);
        }])
            ->orderByDesc('created_at')->limit(10)->get();

        return OfferUpdeteHistoryListResource::collection($lastTenUpdates);
    }

    public function updateMatchUs($offerIdInApi, string|int|float $price, string|int $marketPlaceId): void
    {
        $price = is_string($price) ? PriceService::convertStrToFloat($price) : $price;

        MarketplaceMatchGame::whereOfferId($offerIdInApi)->whereMarketplaceId($marketPlaceId)->first()?->update(['amount_us' => $price]);

    }

    public static function updateApiGameId(MarketplaceMatchGame $match, string $productId, MarketplaceName $marketplace): void
    {
        $ki = new KinguinService();
        $en = new EnebaService();
        $ga = new GamivoService();

        $productName = match ($marketplace->value) {
            MarketplaceName::KINGUIN->value => $ki->getGameNameByProductId($productId),
            MarketplaceName::ENEBA->value => $en->getGameNameByProductId($productId),
            MarketplaceName::GAMIVO->value => $ga->getGameNameByProductId($productId),
            default => null,
        };

        $match->update([
            'api_game_name' => $productName,
        ]);
    }
}
