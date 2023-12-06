<?php

namespace App\Notifications;

use App\Enums\MarketplaceName;
use App\Enums\NotificationTypeEnum;
use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class GeneralRequestNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    protected string $type;

    public function __construct(public readonly bool $success, public readonly string $message, public $gameId, public int|string $marketplaceId, string $type = '')
    {
        $this->type = $type == '' ? NotificationTypeEnum::GENERAL->value : $type;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast', 'database'];
    }

    public function broadcastOn()
    {
        // return new Channel('requestResponse');
        return new Channel('notification');
    }


    public function toBroadcast($notifiable): BroadcastMessage
    {

        return new BroadcastMessage([
            'marketplace' => MarketplaceName::from((int)$this->marketplaceId)->name,
            'message' => $this->message,
            'image' => MarketplaceName::defineImage($this->marketplaceId),
            'success' => $this->success,
            'title' => "{$this->type} Notification",
            'date' => now()->diffForHumans()
        ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'marketplace' => MarketplaceName::from((int)$this->marketplaceId)->name,
            'message' => $this->message,
            'image' => MarketplaceName::defineImage($this->marketplaceId),
            'success' => $this->success,
            'title' => "{$this->type} Notification",
            'game_id' => $this->gameId,
            'date' => now()->diffForHumans()

        ];
    }

}
