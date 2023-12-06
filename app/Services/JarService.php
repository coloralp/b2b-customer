<?php

namespace App\Services;


use App\Enums\CurrencyEnum;
use App\Models\CurrencyChange;
use App\Models\Jar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JarService
{
    public function listAll(array $payload)
    {
        $request = new Request($payload);


        return Jar::when($request->filled('owner_id'), function ($query) use ($request) {
            $query->where('owner_id', $request->input('owner_id'));
        })->when($request->filled('search'), function ($query) use ($request) {
            $like = "%{$request->input('search')}%";
            $query->where('name', 'like', $like);
        })->with('owner');
    }

    public function createDefaultJar($ownerId): void
    {
        Jar::create([
            'owner_id' => $ownerId,
            'name' => Str::random(7),
            'balance' => 0,
            'currency' => CurrencyEnum::EUR->value
        ]);
    }


}
