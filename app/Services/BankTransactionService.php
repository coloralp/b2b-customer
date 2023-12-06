<?php

namespace App\Services;

use App\DTO\CreateBankAccountDTO;
use App\DTO\CreateBankTransactionDTO;
use App\Http\Resources\Api\Bank\BankAccountResource;
use App\Http\Resources\Api\Bank\BankTransactionResource;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;


class BankTransactionService
{

    public function listAll(Request $request)
    {

        return BankTransaction::orderByDesc('created_at')
            ->when(\request()->filled('transaction_type'), function ($query) use ($request) {
                $type = $request->input('transaction_type');
                $query->where('transaction_type', $type);
            });
    }

    public function findById(string|int $id)
    {
        return BankTransaction::findOrFail($id);
    }

    public function createTransaction(CreateBankTransactionDTO $dto)
    {
        return BankTransaction::create($dto->toArray());
    }

    public function updateTransaction(CreateBankTransactionDTO $dto, string|int $transactionId)
    {
        $bankAccount = BankTransaction::findOrFail($transactionId);

        $bankAccount->update($dto->toArray());

        return BankTransaction::findOrFail($transactionId);
    }

    public function deleteTransaction(string|int $transactionId, $reason)
    {
        $transaction = BankTransaction::findOrFail($transactionId);

        try {
            return DB::transaction(function () use ($reason, $transaction) {
                $transaction->update([
                    'delete_reason' => $reason
                ]);
                return $transaction->delete();
            });
        } catch (\Exception $exception) {

        }

    }
}
