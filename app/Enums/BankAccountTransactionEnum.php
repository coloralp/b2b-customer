<?php

namespace App\Enums;

enum BankAccountTransactionEnum: int
{
    case EXPENSE = 1;
    case TRANSFER = 2;

    case INCOME = 3;
}
