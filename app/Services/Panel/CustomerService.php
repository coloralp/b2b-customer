<?php

namespace App\Services\Panel;


use App\Enums\RoleEnum;
use App\Http\Requests\Api\Customer\CreateCustomerRequest;
use App\Http\Requests\Api\Customer\UpdateCustomerRequest;
use App\Http\Requests\Api\Panel\PanelCustomer\PanelCustomerCreateRequest;
use App\Models\RoleOption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    public static function createCustomer(PanelCustomerCreateRequest|CreateCustomerRequest $request): User
    {

        $createData = [
            'first_name' => $request->input('company_name'),
            'last_name' => $request->input('company_name'),
            'name' => $request->input('company_name'),
            'surname' => $request->input('company_name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'is_customer' => 1,
            'currency_id' => $request->input('currency_id'),
            'min_balance' => $request->input('min_balance') ?? 0
        ];


        if (auth()->check()) {
            $filters = [
                'categories' => $request->input('categories') ?? [],
                'category_types' => $request->input('category_types') ?? [],
                'regions' => $request->input('regions') ?? [],
            ];
            $createData = array_merge($createData, [
                'customer_filter' => json_encode($filters)
            ]);
        }



        return User::create($createData);
    }

    public static function updateCustomer(UpdateCustomerRequest $request, $userId)
    {

        return DB::transaction(function () use ($request, $userId) {

            $user = User::with('roleOptions')->findOrFail($userId);

            $updateData = [
                'name' => $request->input('company_name') ?? $user->name,
                'surname' => $request->input('company_name') ?? $user->name,
                'email' => $request->input('email') ?? $user->email,
                //'password' => Hash::make($request->input('password')),
                'min_balance' => $request->input('min_balance') ?? $user->min_balance
            ];

            foreach (User::CUSTOMER_OPTIONS as $CUSTOMER_OPTION) {
                if (!in_array($CUSTOMER_OPTION, ['email', 'password'])) {
                    RoleOption::upsert([
                        'user_id' => $userId,
                        'role' => RoleEnum::CUSTOMER->value,
                        'option' => $CUSTOMER_OPTION,
                        'value' => $request->input($CUSTOMER_OPTION) ?? 'sas'
                    ], ['user_id', 'role', 'option']);
                }
            }
            if (auth()->check()) {
                $filters = [
                    'categories' => $request->input('categories') ?? [],
                    'category_types' => $request->input('category_types') ?? [],
                    'regions' => $request->input('regions') ?? [],
                ];
                $updateData = array_merge($updateData, [
                    'customer_filter' => json_encode($filters)
                ]);
            }


            return $user->update($updateData);
        });
    }
}
