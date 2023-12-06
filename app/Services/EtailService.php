<?php

namespace App\Services;


use App\Models\Game;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EtailService
{
    use ApiTrait;
    public function searchGame($search): array
    {
        $searchGames = Game::orderby('name', 'asc')
            ->select('uuid', 'name', 'id')
            ->where('name', 'like', '%' . $search . '%')
            ->get();

        $response = [];
        foreach ($searchGames as $game) {
            $response[] = [
                "id" => $game->uuid,
                "text" => $game->name,
                "stock" => GameService::getStockKeys($game->id)['active_keys_count'],
            ];

        }

        return $response;
    }

    public function detail($productId): array
    {

        $detail = Game::with(['language', 'region', 'publisher', 'category'])->where('uuid', $productId)->withCount('activeKeys')->first();
        if (!$detail) {
            return [];
        }
        $data = $detail->toArray();

        $data['stock'] = $detail->active_keys_count;
        return $data;
    }


}
