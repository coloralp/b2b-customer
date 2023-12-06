<?php

namespace App\Rules\Customer;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IfVerifyLogin implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!(User::whereEmail($value)->first()?->email_verified_at)) {
            $fail('Your account not verified yet');
        }
    }
}
