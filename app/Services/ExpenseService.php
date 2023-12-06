<?php

namespace App\Services;

use App\Enums\OfferStatus;
use App\Http\Requests\Api\Expense\CreateExpenseRequest;
use App\Http\Requests\Api\Expense\ListExpenseRequest;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\Key;
use App\Models\Offer;
use App\Models\Summary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ExpenseService
{

    public function list(array $payload)
    {
        $request = new ListExpenseRequest($payload);

        return Expense::query()
            ->when($request->filled('who'), function (Builder $query) use ($request) {
                $query->where('who', $request->input('who'));
            })->when($request->filled('amount_less'), function (Builder $query) use ($request) {
                $query->where('amount', '>=', $request->input('amount_less'));
            })->when($request->filled('amount_max'), function (Builder $query) use ($request) {
                $query->where('amount', '<=', $request->input('amount_max'));
            })->when($request->filled('amount_currency'), function (Builder $query) use ($request) {
                $query->where('amount_currency', $request->input('amount_currency'));
            })->when($request->filled('description'), function (Builder $query) use ($request) {
                $query->where('description', 'like', "%{$request->input('amount_currency')}%");
            })->when($request->filled('start'), function (Builder $query) use ($request) {
                $query->where('created_at', '>=', $request->input('start'));
            })->when($request->filled('finish'), function (Builder $query) use ($request) {
                $query->where('created_at', '<=', $request->input('finish'));
            })->orderByDesc('created_at');
    }

    public function addExpense(CreateExpenseRequest $request): int
    {
        $data = $request->validated();
        $currency = Currency::findOrFail($request->input('amount_currency'));
        $convertEuro = CurrencyService::convertEur($request->input('amount'), $currency);
        $currencyInfo = CurrencyService::learnCurrency();

        $data = array_merge($data, [
            'amount_convert_eur' => $convertEuro,
            'currency_info_id' => $currencyInfo->id
        ]);

        $expense = Expense::create($data);

        $summary = Summary::whereMonthId(now()->month)->whereYearId(now()->year);

        if ($summary->exists()) {
            $summaryFirst = $summary->first();
            $summaryFirst->update([
                'add_cost' => ($summaryFirst->add_cost) + $expense->amount_convert_eur
            ]);
        }

        return $expense->id;

    }

    public function updateExpense(CreateExpenseRequest $request, $expenseId): int
    {

        $expense = Expense::findOrFail($expenseId);

        $data = $request->validated();
        $currency = Currency::findOrFail($request->input('amount_currency'));

        if (($expense->amount != $request->input('amount')) or ($expense->amount_currency != $request->input('amount_currency'))) {
            $convertEuro = CurrencyService::convertEur($request->input('amount'), $currency, $expense->created_at);
        } else {
            $convertEuro = $expense->amount_convert_eur;
        }

        $currencyInfo = CurrencyService::learnCurrency($expense->created_at);

        $data = array_merge($data, [
            'amount_convert_eur' => $convertEuro,
            'currency_info_id' => $currencyInfo->id
        ]);

        $expense->update($data);

        return $expenseId;
    }

    public function deleteExpense($expenseId): bool
    {

        Expense::findOrFail($expenseId)->delete();

        return true;
    }

    public static function getTotalExpenseByDate($start, $end = null): float
    {
        return PriceService::convertStrToFloat(
            Expense::getExpenseByDate($start, $end)->sum('amount_convert_eur')
        );
    }

}
