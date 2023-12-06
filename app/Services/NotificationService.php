<?php

namespace App\Services;

use App\Enums\MarketplaceName;
use App\Enums\NotificationTypeEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Notifications\GeneralRequestNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    public static function getUnreadData($userIid)
    {
        $notifications = User::findOrFail($userIid)->unreadNotifications->sortByDesc('created_at')->take(5);

        return self::getUnreadData($notifications);
    }

    private function getData($notifications): array
    {
        $data = [];


        foreach ($notifications as $notification) {

            $data[] = [
                'id' => $notification->id,
                'message' => $notification->data['message'],
                'title' => $notification->data['title'],
                'marketplace' => $notification->data['marketplace'],
                'image' => $notification->data['image'],
                'date' => $notification->created_at->diffForHumans(),
                'isRead' => !is_null($notification->read_at)
            ];
        }

        return $data;
    }

    public static function sendGeneralNotification(string|int $gameId, string $message, int|string $marketPlaceId, $isSuccess, Collection $users = null): void
    {

        if ($isSuccess === -1) {
            $isSuccess = false;
        }


        if ($isSuccess) {
            $users = $users ?? User::role([RoleEnum::MANAGER->value, RoleEnum::MARKETING->value])->get();
            Log::channel(MarketplaceName::defineSuccessLog($marketPlaceId))->info($message);
        } else {
            $users = $users ?? User::role([RoleEnum::BACKEND_DEVELOPER->value, RoleEnum::MANAGER->value, RoleEnum::MARKETING->value])->get();
            Log::channel(MarketplaceName::defineErrorLog($marketPlaceId))->error($message);
        }

        Notification::send($users, new GeneralRequestNotification($isSuccess, $message, $gameId, $marketPlaceId));
    }

    public function writeAndSendLog(string|int $gameId, string $message, int|string $marketPlaceId, $isSuccess, NotificationTypeEnum $notificationTypeEnum, Collection $users = null): void
    {

        if ($isSuccess === -1) {
            $isSuccess = false;
        }

        if ($isSuccess) {
            $users = $users ?? User::role([RoleEnum::MANAGER->value, RoleEnum::MARKETING->value])->get();
            Log::channel(MarketplaceName::defineSuccessLog($marketPlaceId))->info($message);
        } else {
            $users = $users ?? User::role([RoleEnum::BACKEND_DEVELOPER->value, RoleEnum::MANAGER->value,RoleEnum::MARKETING->value])->get();
            Log::channel(MarketplaceName::defineErrorLog($marketPlaceId))->error($message);
        }

        Notification::send($users, new GeneralRequestNotification($isSuccess, $message, $gameId, $marketPlaceId, $notificationTypeEnum->value));
    }
}
