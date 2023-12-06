<?php

namespace App\Interfaces;

use App\Models\MarketplaceMatchGame;

interface IMarketplace
{
    public static function errorLog(): string;

    public static function successLog(): string;

    public function getApiUrl(): string;

    public function getToken(): mixed;

    public function refreshToken(): void;

    public function searchGame(string $search): array;

    public function ifOfferExists($productId): array;

    public function matchWithUs(array $payload, $userId = null): mixed;

    public function changeStatus($offerIdInApi, $status, $stock = 0): bool;

    public function updateOffer(array $payload): bool;

    public function updateStock($offerId, $gameId): bool;

    public static function getAlreadyExists(): string;

    public function getLowestPrice(int|string $productId): float;

    public function getGameNameByProductId(string $productId): ?string;

    public function createIfNotExistUs($productApiId, $gameId): MarketplaceMatchGame|null;

}
