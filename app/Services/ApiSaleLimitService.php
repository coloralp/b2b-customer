<?php

namespace App\Services;

use App\Http\Requests\Api\ApiSaleLimit\ApiSaleLimitlistRequest;
use App\Models\ApiSaleLimit;
use Illuminate\Database\Eloquent\Builder;

class ApiSaleLimitService
{
    public function listAll(array $payload)
    {
        $request = new ApiSaleLimitlistRequest($payload);

        return ApiSaleLimit::query()
            ->when($request->filled('marketplace_id'), function (Builder $query) use ($request) {
                $query->where('marketplace_id', $request->input('marketplace_id'));
            })
            ->when($request->filled('game_id'), function (Builder $query) use ($request) {
                $query->where('game_id', $request->input('game_id'));
            });
    }
}
