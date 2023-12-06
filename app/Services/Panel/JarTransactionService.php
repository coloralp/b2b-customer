<?php

namespace App\Services\Panel;

use App\DTO\PaginateDTO;
use App\Http\Requests\Api\Panel\JarTransaction\JarTransactionListRequest;
use App\Models\Jar;
use Illuminate\Database\Query\Builder;

class JarTransactionService
{
    public function getMyJar(array $payload): Jar|null
    {
        $request = new JarTransactionListRequest($payload);

        $paginate = new PaginateDTO($request);

        $perPage = $paginate->getPerPage();
        $currentPage = $paginate->getCurrentPage();


        return Jar::with(['jarTransactions' => function (Builder $query) use ($currentPage, $perPage, $request) {
            $query
                ->when($request->filled('amount_currency'), function (Builder $query) use ($request) {
                    $query->where('amount_currency', $request->input('amount_currency'));
                })
                ->when($request->filled('amount_less'), function (Builder $query) use ($request) {
                    $query->where('amount', '>=', $request->input('amount_less'));
                })
                ->when($request->filled('amount_most'), function (Builder $query) use ($request) {
                    $query->where('amount', '<=', $request->input('amount_most'));
                })->simplePaginate($perPage, ['*'], 'pages', $currentPage);
        }])->whereOwnerId($request->input('who'))->first();
    }
}
