<?php

namespace App\Services;


use App\Enums\CurrencyEnum;
use App\Enums\JarTransactionEnum;
use App\Enums\JarTransactionRequestEnum;
use App\Enums\RoleEnum;
use App\Enums\TransactionStatusEnum;
use App\Http\Requests\Api\JarTransaction\AddedKeysListTransactionRequest;
use App\Http\Requests\Api\JarTransactionRequest\AdminListTransactionRequest;
use App\Models\Currency;
use App\Models\ExchangeInfo;
use App\Models\Jar;
use App\Models\JarTransaction;
use App\Models\JarTransactionsRequest;
use App\Models\Key;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class JarTransactionService
{
    public function listAll(array $payload)
    {

        $request = new AdminListTransactionRequest($payload);

        return JarTransaction::query()
            ->when($request->filled('jar_id'), function ($query) use ($request) {
                $query->where('jar_id', $request->input('jar_id'));
            }, function ($query) {
                $query->whereNot('jar_id', 8);
            })->when($request->filled('processed_by'), function ($query) use ($request) {
                $query->where('processed_by', $request->input('processed_by'));
            })
            ->whereBetween('created_at', [Carbon::parse($request->input('start')), Carbon::parse($request->input('end'))])
            ->when($request->filled('amount_currency'), function ($query) use ($request) {
                $query->where('amount_currency', '>=', $request->input('amount'));
            })->when($request->filled('amount_less'), function (Builder $query) use ($request) {
                $query->where('amount', '>=', $request->input('amount_less'));
            })->when($request->filled('amount_most'), function (Builder $query) use ($request) {
                $query->where('amount', '<=', $request->input('amount_most'));
            })->orderByDesc('id');
    }

    public function createTransaction(array $payload): int
    {
        $amount = $payload['amount'];
        $amountCurrency = Currency::findOrFail($payload['amount_currency']);

        $jar = Jar::findOrFail($payload['jar_id']);
        $from = CurrencyEnum::from($payload['amount_currency']);
        $to = CurrencyEnum::from($jar->currency->value);


        $transactionData = [
            'currency_info_id' => $payload['currency_info_id'] ?? $this->getCurrentExchange()->id,
            'amount_convert_eur' => $payload['amount_convert_eur'] ?? $this->convertEur($amount, $amountCurrency),
            'amount_convert_jar' => $payload['amount_convert_jar'] ?? $this->convertJarCurrency($from, $to, $amount),
            'description' => $payload['description'] ?? now()->format('d.m.Y H:i:s')
        ];


        $createData = array_merge($payload, $transactionData);


        try {
            return DB::transaction(function () use ($createData, $jar) {

                $type = $createData['jar_transaction_type'];
                $oldBalance = $jar->balance;


                $newBalance = $this->findNewBalanceWithType($type, $jar, $createData['amount_convert_jar']);

                $createData = array_merge($createData, ['new_balance' => $newBalance, 'old_balance' => $oldBalance]);

                $transaction = JarTransaction::create($createData);


                $jar->update(['balance' => $newBalance]);

                return $transaction->id;
            });
        } catch (\Exception $exception) {
            dd($exception);
        }
    }


    public function getAddedKeysAsGroup(array $payload)
    {

        $request = new AddedKeysListTransactionRequest($payload);

        return JarTransaction::whereHas('keys')->withCount('keys')->whereNotNull('game_id')->whereBuyKey(1)
            ->when($request->filled('start'), function (Builder $query) use ($request) {
                $query->where('created_at', '>=', $request->input('start'));
            })->when($request->filled('finish'), function (Builder $query) use ($request) {
                $query->where('created_at', '<=', $request->input('finish'));
            })
            ->when($request->filled('jar_id'), function ($query) use ($request) {
                $query->where('jar_id', $request->input('jar_id'));
            }, function ($query) {
                $query->$query->where('jar_id', '!=', 8);
            })->when($request->filled('processed_by'), function ($query) use ($request) {
                $query->where('processed_by', $request->input('processed_by'));
            })
            ->when($request->filled('game_id'), function ($query) use ($request) {
                $query->where('game_id', $request->input('game_id'));
            })->orderByDesc('created_at');

    }

    public function getCurrentExchange(): ExchangeInfo
    {
        return CurrencyService::learnCurrency();
    }

    public function convertEur($amount, Currency $currentCurrency): float
    {
        return CurrencyService::convertEur($amount, $currentCurrency);
    }

    public function convertJarCurrency(CurrencyEnum $from, CurrencyEnum $to, $amount): float
    {
        return CurrencyService::exchange($from, $to, $amount);
    }

    public function findNewBalanceWithType($type, Jar $jar, $convertCurrentJar): float
    {
        return match ($type) {
            JarTransactionEnum::INCOME->value => (($jar->balance) + $convertCurrentJar),
            JarTransactionEnum::EXPENSE->value => (($jar->balance) - $convertCurrentJar)
        };
    }

    public function createTransactionFromRequest(JarTransactionsRequest $transactionsRequest): void
    {
        $oldBalance = $transactionsRequest->jar->balance;
        $newBalance = ($transactionsRequest->jar->balance) + $transactionsRequest->will_add_amount;

        JarTransaction::create([
            'jar_id' => $transactionsRequest->jar_id,
            'old_balance' => $oldBalance,
            'new_balance' => $newBalance,
            'processed_by' => $transactionsRequest->processed_by,
            'amount' => $transactionsRequest->amount,
            'amount_currency' => $transactionsRequest->amount_currency->value,
            'jar_transaction_type' => JarTransactionEnum::INCOME->value,
            'description' => $transactionsRequest->description ?? now(),
            'amount_convert_eur' => $transactionsRequest->amount_convert_eur,
            'amount_convert_jar' => $transactionsRequest->will_add_amount,
            'currency_info_id' => $transactionsRequest->currency_info_id,
            'created_at' => Carbon::parse($transactionsRequest->created_at),
            'updated_at' => now(),
        ]);


        $transactionsRequest->jar()->update([
            'balance' => $newBalance
        ]);

        $transactionsRequest->update(['status' => JarTransactionRequestEnum::APPROVED->value]);
    }

    public function defineTransactionForKeys($transactionId, array $keyIds): void
    {
        if (!empty($keyIds)) {
            Key::whereIn('id', $keyIds)->update(['jar_transaction_id' => $transactionId]);
        }
    }


    public function createNewJarTransactionWithKeys(Collection|\Illuminate\Support\Collection $keys, CurrencyEnum $to, $currentKeyId = null): float|int
    {

        $totalAsJar = 0;
        /** @var Key $key */

        if ($to->value == CurrencyEnum::EUR->value) {
            return PriceService::convertFloat($keys->sum('cost_convert_euro'));
        }

        foreach ($keys as $key) {
            $from = CurrencyEnum::from($key->cost_currency_id);

            if ($key->id == $currentKeyId) {
                $subAsJar = CurrencyService::exchange($from, $to, $key->cost);
            } else {
                $subAsJar = CurrencyService::exchange($from, $to, $key->cost, $key->created_at);
            }

            $totalAsJar += $subAsJar;

        }
        return $totalAsJar;
    }

    public function defineJarId(User $currentSupplier): mixed
    {
        return is_null($currentSupplier->jar) ? Jar::whereOwnerId(User::role(RoleEnum::MANAGER->value)->whereEmail(Jar::OUR_JAR)->first()?->id)->first()->id : $currentSupplier->jar->id;
    }

    public function defineJarCurrency(User $currentSupplier): CurrencyEnum
    {
        return is_null($currentSupplier->jar) ? CurrencyEnum::EUR : $currentSupplier->jar->currency;
    }


    public function takeExpenseFromSupplier(User $currentSupplier, JarTransaction $currentTransaction, int $processBy): array
    {
        $jarId = $this->defineJarId($currentSupplier);

        if (!$jarId) {
            return new \Exception('Transactionlarda key güncellerekn jar bulunamadı!');
        }


        return [
            "jar_id" => $jarId,
            "processed_by" => $processBy,
            "amount" => $currentTransaction->amount,
            "amount_currency" => $currentTransaction->amount_currency->value,
            "jar_transaction_type" => JarTransactionEnum::INCOME->value,
            "description" => "keye yanlış veri eklemeden dolayı",
        ];
    }

    public function ifWerAreDept(User $currentSupplier, JarTransaction $currentTransaction, float|int $amount, $processBy): array
    {
        $jarId = $this->defineJarId($currentSupplier);

        if (!$jarId) {
            return new \Exception('Transactionlarda key güncellerekn jar bulunamadı!');
        }

        return [
            "jar_id" => $jarId,
            "processed_by" => $processBy,
            "amount" => $amount,
            "amount_currency" => $currentTransaction->amount_currency->value,
            "jar_transaction_type" => JarTransactionEnum::INCOME->value,
            "description" => "Güncellemeden dolayı öncekinden küçk bir değer verildi ve bu suppliere borçu durumumuz!",
        ];
    }

    public function createAgainTransaction(User $currentSupplier, int $processBy, float|int $newTotalAsJar, $description = null): array
    {

        $jarId = $this->defineJarId($currentSupplier);

        if (!$jarId) {
            return new \Exception('Transactionlarda key güncellerekn jar bulunamadı!');
        }

        return [
            "jar_id" => $jarId,
            "processed_by" => $processBy,
            "amount" => $newTotalAsJar,
            "amount_currency" => $this->defineJarCurrency($currentSupplier)->value,
            "jar_transaction_type" => JarTransactionEnum::EXPENSE->value,
            "description" => $description ?? "Key oluştururken yanlış değerler verildi!Geri iade edilip teakrar oluşturuldu",
        ];
    }


    public function addExpenseAgain(Key|Collection $keys, $isSiteOwner = true): int
    {

        if ($keys instanceof Collection) {
            $otherKeyId = $keys->first()->id;
            $otherKey = Key::with(['supplier.jar'])->findOrFail($otherKeyId);
            $newTotalAsJar = $this->createNewJarTransactionWithKeys($keys, $this->defineJarCurrency($otherKey->supplier), $otherKey->id);
            $expenseTransactionData = $this->createAgainTransaction($otherKey->supplier, auth()->id(), $newTotalAsJar);
            return $this->createTransaction($expenseTransactionData);
        } else {
            $keysSameTime = $keys->buysInSameTime;
            $updatesKeys = $isSiteOwner ? $keysSameTime : $keysSameTime->where('id', '!=', $keys->id);
            $otherKeyId = $updatesKeys->first()->id;
            $otherKey = Key::with(['supplier.jar'])->findOrFail($otherKeyId);
            $newTotalAsJar = $this->createNewJarTransactionWithKeys($updatesKeys, $this->defineJarCurrency($otherKey->supplier), $keys->id);
            $expenseTransactionData = $this->createAgainTransaction($otherKey->supplier, auth()->id(), $newTotalAsJar);
            return $this->createTransaction($expenseTransactionData);
        }
    }

    public static function createTransactionCode(): string
    {
        return date('dmYHis') . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5));
    }

}
