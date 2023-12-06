<?php

namespace App\Services\Panel;

use App\Enums\CurrencyEnum;
use App\Http\Requests\Api\JarTransactionRequest\AdminListTransactionRequest;
use App\Http\Requests\Api\Panel\JarTransactionRequest\JarTransactionRequestListRequest;
use App\Models\Currency;
use App\Models\Jar;
use App\Models\JarTransaction;
use App\Models\JarTransactionsRequest;
use App\Services\CurrencyService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use App\Services\JarTransactionService;

class JarTransactionRequestService
{

    public function createTransaction(array $payload): int
    {

        $amount = $payload['amount'];
        $amountCurrency = Currency::findOrFail($payload['amount_currency']);


        $jar = Jar::findOrFail($payload['jar_id']);
        $from = CurrencyEnum::from($payload['amount_currency']);
        $to = CurrencyEnum::from($jar->currency->value);

        $jarTransactionService = app(abstract: JarTransactionService::class);

        $transactionData = [
            'currency_info_id' => $jarTransactionService->getCurrentExchange()->id,
            'amount_convert_eur' => $jarTransactionService->convertEur($amount, $amountCurrency),
            'will_add_amount' => $jarTransactionService->convertJarCurrency($from, $to, $amount),
            'description' => $payload['description'] ?? now()->format('d.m.Y H:i:s')
        ];


        $createData = array_merge($payload, $transactionData);


        return JarTransactionsRequest::create($createData)->id;


    }

    public function list(array $payload, $perPage, $currentPage)
    {
        $request = new JarTransactionRequestListRequest($payload);

        return Jar::with(['jarTransactionRequests' => function ($query) use ($currentPage, $perPage, $request) {
            $query->where('jar_id', $request->input('jar_id'))
                ->where('jar_transaction_type', $request->input('jar_transaction_type'))
                ->when($request->filled('amount_currency'), function (Builder $query) use ($request) {
                    $query->where('amount_currency', $request->input('amount_most'));
                })
                ->when($request->filled('status'), function (Builder $query) use ($request) {
                    $query->where('status', $request->input('status'));
                })
                ->when($request->filled('amount_less'), function (Builder $query) use ($request) {
                    $query->where('amount', '>=', $request->input('amount_less'));
                })->when($request->filled('amount_most'), function (Builder $query) use ($request) {
                    $query->where('amount', '<=', $request->input('amount_most'));
                })->simplePaginate($perPage, ['*'], 'pages', $currentPage);
        }])->findOrFail($request->input('jar_id'));

    }

    public function adminList($payload)
    {
        $request = new AdminListTransactionRequest($payload);

        return JarTransactionsRequest::query()
            ->when($request->filled('jar_id'), function (Builder $query) use ($request) {
                $query->where('jar_id', $request->input('jar_id'));
            })
            ->when($request->filled('jar_transaction_type'), function (Builder $query) use ($request) {
                $query->where('jar_transaction_type', $request->input('amount_most'));
            })
            ->when($request->filled('amount_currency'), function (Builder $query) use ($request) {
                $query->where('amount_currency', $request->input('amount_most'));
            })
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('amount_less'), function (Builder $query) use ($request) {
                $query->where('amount', '>=', $request->input('amount_less'));
            })->when($request->filled('amount_most'), function (Builder $query) use ($request) {
                $query->where('amount', '<=', $request->input('amount_most'));
            });
    }
}
