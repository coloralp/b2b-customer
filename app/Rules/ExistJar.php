<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistJar implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = request()->all();


        $jsonData = $data['components'][0]['snapshot'];

        $jsonArray = json_decode($jsonData, 1);

        $email = $jsonArray['data']['data'][0]['email'] ?? null;

        if (!$email) {
            $fail(__('orders.went_wrong'));
        }

        $email = trim($email);


        $user = User::whereEmail($email);
        if (!$user->exists() or is_null($user->first()->jar)) {
            $fail('Yo have to have one moneybox.Please contact with us!');
        }
    }
}
