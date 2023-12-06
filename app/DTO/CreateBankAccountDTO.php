<?php

namespace App\DTO;

use App\Http\Requests\Api\BankAccount\CreateBankAccountRequest;
use App\Http\Requests\Api\BankAccount\UpdateBankAccountRequest;
use Illuminate\Contracts\Support\Arrayable;

class CreateBankAccountDTO implements Arrayable
{

    public function __construct(public string $name, public float $balance, public string|int $bankId)
    {

    }

    public static function fromRequest(CreateBankAccountRequest|UpdateBankAccountRequest $request): CreateBankAccountDTO
    {
        return new static($request->input('name'), $request->input('balance'), $request->input('bank_id'));
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'balance' => $this->balance,
            'bank_id' => $this->bankId
        ];
    }
}
