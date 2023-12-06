<?php

namespace App\Enums;

enum TransactionStatusEnum: int
{
    case FAILED = 1;
    case SUCCESS = 2;
    case PENDING = 3;

}
