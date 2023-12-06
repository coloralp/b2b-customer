<?php

namespace App\Exceptions;

use App\Enums\CustomExceptionCode;

class UserException extends InternalException
{
    public static function exception(): UserException
    {
        return static::new(CustomExceptionCode::SOME_EXCEPTION_1);
    }
}
