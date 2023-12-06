<?php

namespace App\Enums;

enum PaymentTypeEnum: int
{
    case USD_ACCOUNT = 1;
    case EUR_ACCOUNT = 2;
    case TRY_ACCOUNT = 3;
    case PAYPAL = 4;
    case BITCOIN = 5;

}
