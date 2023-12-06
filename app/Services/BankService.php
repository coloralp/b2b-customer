<?php

namespace App\Services;

use App\Models\Bank;


class BankService
{

    public function listAll()
    {
        return Bank::orderByDesc('created_at')
            ->when(\request()->filled('search'), function ($query) {
                $search = trim(\request()->input('search'));
                $query->where('name', 'like', "%$search%");
            });
    }

    public function findById(string|int $id)
    {
        return Bank::findOrFail($id);
    }

    public function createBank(string $name)
    {
        return Bank::create(['name' => $name]);
    }

    public function updateBank(string|int $bankId, string $name)
    {
        $bank = Bank::findOrFail($bankId);

        $bank->update(['name' => $name]);

        return Bank::findOrFail($bankId);
    }

    public function deleteBank(string|int $bankId): ?bool
    {
        $bank = Bank::findOrFail($bankId);;

        return $bank->delete();
    }

}
