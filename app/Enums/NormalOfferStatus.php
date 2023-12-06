<?php

namespace App\Enums;

enum NormalOfferStatus: int
{
    case APPROVED = 1;
    case PENDING = 2;

    case REJECT = 3;
}
