<?php

namespace App\Enums;

enum WhyStockUpdate: int//BUNLAR STOĞUN DEĞİŞMESİNE NEDEN OLACAK İŞLEMELR
{
    case ADD_KEY = 1;
    case DELETE_KEY = 2;
    case DELETE_MULTIPLE_KEY = 3;

    case MANUAL_ORDER_CREATE = 4;

    case API_ORDER_CREATE = 5;

    case ORDER_CHANGE_STATUS = 6;
}
