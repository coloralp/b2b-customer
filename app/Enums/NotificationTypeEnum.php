<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case UPDATE_STOCK = 'UpdateStock';
    case CHANGE_OFFER_STATUS_JOB = 'ChangeOfferStatusJob';

    case MATCH = 'Match';

    case GENERAL = 'Genaral';
    case Archive = 'Archive';
    case Restore = 'Restore';
    case STOCK_NOTIFICATION = 'StockNotification';

    case AUTO_UPDATE_PRICE = 'AutoUpdatePrice';
}
