<?php

namespace App\Services;

use App\DTO\CreateBankAccountDTO;
use App\Http\Resources\Api\Bank\BankAccountResource;
use App\Models\BankAccount;


class BankAccountService
{

    public function listAll()
    {
        return BankAccount::orderByDesc('created_at')
            ->when(\request()->filled('search'), function ($query) {
                $search = trim(\request()->input('search'));
                $query->where('name', 'like', "%$search%");
            });
    }

    public function findById(string|int $id)
    {
        return BankAccountResource::make(BankAccount::findOrFail($id));
    }

    public function createBankAccount(CreateBankAccountDTO $dto)
    {
        return BankAccountResource::make(BankAccount::create($dto->toArray()));
    }

    public function updateBank(CreateBankAccountDTO $dto, string|int $bankAccountId)
    {
        $bankAccount = BankAccount::findOrFail($bankAccountId);

        $bankAccount->update($dto->toArray());

        return BankAccountResource::make(BankAccount::findOrFail($bankAccountId));
    }

    public function deleteBank(string|int $bankId): ?bool
    {
        $bank = BankAccount::findOrFail($bankId);;

        return $bank->delete();
    }

}
