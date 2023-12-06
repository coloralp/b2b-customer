<?php

namespace App\Services;

use App\Enums\KeyStatus;
use App\Models\MainKeyHistory;
use App\Traits\AccountSummaryTrait;

class KeyStatusHistoryService
{
    use AccountSummaryTrait;

    public function insertData(string|int $gameId, array $keyIds, KeyStatus $keyStatus, string|int $orderId = null, string $desc = null): void
    {
        foreach ($keyIds as $keyId) {
            $parentId = MainKeyHistory::getParents()->whereKeyId($keyId)->first()?->id;

            MainKeyHistory::create([
                'parent_id' => $parentId,
                'key_id' => $keyId,
                'status' => $keyStatus->value,
                'order_id' => $orderId,
                'created_at' => now(),
                'desc' => $desc
            ]);
        }
    }

}
