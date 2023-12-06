<?php

namespace App\Exceptions;

use App\Enums\CustomExceptionCode;
use Exception;

class InternalException extends Exception
{

    protected string $description;

    protected CustomExceptionCode $exceptionCode;


    public static function new(CustomExceptionCode $exceptionCode, string $message = null, string $description = null, int $statusCode = 0): static
    {
        $exception = new static(
            $message ?? $exceptionCode->getMessage(),
            $statusCode ?? $exceptionCode->value
        );

        $exception->exceptionCode = $exceptionCode;
        $exception->description = $description ?? $exceptionCode->getDescription();


        return $exception;
    }

    public function getInternalCode(): CustomExceptionCode
    {
        return $this->exceptionCode;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

}
