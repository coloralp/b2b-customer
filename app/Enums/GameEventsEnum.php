<?php

namespace App\Enums;

enum GameEventsEnum: int
{
    case CREATED = 1;
    case UPDATED = 2;
    case DELETED = 3;
}
