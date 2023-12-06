<?php

namespace App\Enums;

use Ramsey\Uuid\Type\Integer;

enum SalesPeriod: int
{
    case YESTERDAY_SALES =1;
    case TODAY_SALES = 2;
    case THIS_WEEK_SALES=3;
    case THIS_MONTH_SALES=4;
    case THIS_YEAR_SALES = 5;
    case LAST_WEEK_AND_THIS_WEEK_SALES = 6;
    case LAST_MONTH_SALES = 7;
    case GENERAL_SALES = 8;



}
