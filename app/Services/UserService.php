<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Http\Resources\Api\Permission\PermissionResource;
use App\Http\Resources\Api\Role\RoleResource;
use App\Models\User;
use App\Models\UserTwoFactory;
use App\Traits\ApiTrait;
use Spatie\Permission\Models\Role;

class UserService
{
    use ApiTrait;

    public static function generateTwFactoryCode(): int
    {
        $twoFactoryCode = $randomNumber = mt_rand(1000, 9999);
        do {
            $twoFactoryCode = $randomNumber = mt_rand(1000, 9999);
        } while (UserTwoFactory::where('two_factory', $twoFactoryCode)->exists());
        return $twoFactoryCode;
    }

    public static function generateResetCode(): int
    {
        $resetCode = $randomNumber = mt_rand(1000, 9999);
        do {
            $twoFactoryCode = $randomNumber = mt_rand(1000, 9999);
        } while (User::where('reset_code', $resetCode)->exists());
        return $resetCode;
    }


    public static function sendNotificationUsers()
    {
        return User::role(RoleEnum::BACKEND_DEVELOPER->value, 'api')->get();
    }

    public function getAllPermissions(int|string $userId): \Illuminate\Http\JsonResponse
    {

        $wthoutRolles = Role::whereIn('name', [RoleEnum::SUPPLIER->value, RoleEnum::CUSTOMER->value, RoleEnum::PUBLISHER->value])->get();
        $user = User::withoutRole($wthoutRolles)->with('permissions')->findOrFail($userId);


        return $this->apiSuccessResponse([
            'with_roles' => RoleResource::collection($user->roles),
            'direct_permissions' => PermissionResource::collection($user->permissions)
        ]);
    }

}
