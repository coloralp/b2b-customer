<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\UserTwoFactory;

class TwoFactoryService
{
    public function createTwoFactory(int $userId,int $twoFactoryCode): void
    {
        UserTwoFactory::create([
           'who' => $userId,
           'two_factory' => $twoFactoryCode
        ]);

    }

    public function deleteTwoFactory(int $userId,$twoFactoryId): void
    {
        UserTwoFactory::where('who', $userId)
            ->where('id', $twoFactoryId)
            ->delete();
    }
}
