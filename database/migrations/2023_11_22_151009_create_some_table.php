<?php

use App\Enums\RoleEnum;
use App\Models\RoleOption;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $customerRole = \Spatie\Permission\Models\Role::whereName(\App\Enums\RoleEnum::CUSTOMER->value)->first();

        foreach (\App\Enums\MarketplaceName::cases() as $case) {
            if ($case->getEmail() != -1) {
                DB::transaction(function () use ($case) {
                    $my = \App\Models\User::role(\App\Enums\RoleEnum::CUSTOMER)->where('name', $case->defineCustomerName());

                    if ($my->doesntExist()) {
                        $customer = \App\Models\User::create([
                            'email' => $case->getEmail(),
                            'password' => \Illuminate\Support\Facades\Hash::make($case->getEmail()),
                            'min_balance' => 0,
                            'name' => $case->defineCustomerName(),
                            'first_name' => $case->defineCustomerName(),
                            'last_name' => $case->defineCustomerName(),
                            'surname' => $case->defineCustomerName()
                        ]);

                        $customer->assignRole(RoleEnum::CUSTOMER->value);

                        $data = ['email' => $case->getEmail(), 'password' => \Illuminate\Support\Facades\Hash::make($case->getEmail()), 'location' => $case->name, 'company_name' => $case->name, 'address' => $case->name, 'vat_number' => 123, 'company_registration_number' => '123', 'web_site' => 'asa', 'info' => $case->name, 'payment_method' => 'api'];

                        foreach (User::CUSTOMER_OPTIONS as $CUSTOMER_OPTION) {
                            if ($CUSTOMER_OPTION != 'password') {
                                RoleOption::create([
                                    'user_id' => $customer->id,
                                    'role' => RoleEnum::CUSTOMER->value,
                                    'option' => $CUSTOMER_OPTION,
                                    'value' => $data[$CUSTOMER_OPTION] ?? ''
                                ]);
                            }
                        }
                    } else {
                        $my->update(['email' => $case->getEmail()]);
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('some');
    }
};
