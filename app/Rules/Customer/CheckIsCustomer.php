<?php

namespace App\Rules\Customer;

use App\Enums\RoleEnum;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIsCustomer implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!User::whereEmail($value)?->first()?->hasRole(RoleEnum::CUSTOMER)) {
            $fail('here only customers can login');
        }
    }
}
