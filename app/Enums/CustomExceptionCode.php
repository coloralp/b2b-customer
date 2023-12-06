<?php

namespace App\Enums;

enum CustomExceptionCode: int
{

    case SOME_EXCEPTION_1 = 1;
    case SOME_EXCEPTION_2 = 2;


    public function getMessage(): string
    {
        $key = "exceptions.{$this->value}.message";

        $translation = __($key);

        if ($translation == $key) {
            return "Something went wrong!";
        }

        return $translation;
    }

    public function getDescription(): string
    {
        $key = "exceptions.{$this->value}.description";

        $translation = __($key);

        if ($translation == $key) {
            return "Something went wrong!";
        }

        return $translation;
    }
}
