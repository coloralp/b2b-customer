<?php

namespace App\DTO;

use App\Http\Requests\Api\BankTransaction\CreateBankTransaction;
use Illuminate\Contracts\Support\Arrayable;

class CreateBankTransactionDTO implements Arrayable
{

    public string|int $addedBy;
    public string|int $userBy;

    public function __construct(
        public string|int $bankAccountId,
        public string|int $transactionType,
        public string     $description,
        string|int        $addedBy = null,
        string|int        $userBy = null,

    )
    {
        $this->addedBy = $addedBy;
        $this->userBy = $userBy;
    }

    public static function fromRequest(CreateBankTransaction $request): CreateBankTransactionDTO
    {
        return new static(
            $request->input('bank_account_id'),
            $request->input('transaction_type'),
            $request->input('description'),
            $request->input('added_by'),
            $request->input('user_by')
        );
    }

    public function toArray(): array
    {
        return [
            'bank_account_id' => $this->bankAccountId,
            'transaction_type' => $this->transactionType,
            'description' => $this->description,
            'added_by' => $this->addedBy,
            'user_by' => $this->userBy
        ];
    }
}
