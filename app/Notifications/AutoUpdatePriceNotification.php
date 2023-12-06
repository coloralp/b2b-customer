<?php

namespace App\Notifications;

use App\Enums\MarketplaceName;
use App\Enums\NotificationTypeEnum;
use App\Models\MarketPlace;
use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AutoUpdatePriceNotification extends Notification
{

    public function __construct(public readonly MarketplaceName $marketPlace, public $message, public $gameId, public $success)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function broadcastOn()
    {
        // return new Channel('requestResponse');
        return new Channel('notification');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'marketplace' => $this->marketPlace->name,
            'message' => $this->message,
            'image' => MarketplaceName::defineImage($this->marketPlace->value),
            'process' => NotificationTypeEnum::AUTO_UPDATE_PRICE->value,
            'game_id' => $this->gameId,
            'success' => $this->success,
            'title' => 'Auto Update Price Notification',
            'date' => now()->diffForHumans()

        ];
    }
}
