<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ReturnEmptyKeysException extends Exception
{

    public function  __construct($message = "", $code = 404, Throwable $previous = null)
    {
        $message = $message ?: __("Return isteğinde durumu değiştirilmek istenen keyleri bulurken key arrayinin boş gelmesi durumunda fırlatılır");

        parent::__construct($message, $code, $previous);
    }
}
