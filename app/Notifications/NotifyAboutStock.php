<?php

namespace App\Notifications;

use App\Enums\MarketplaceName;
use App\Enums\NotificationTypeEnum;
use App\Models\MarketPlace;
use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NotifyAboutStock extends Notification
{
    use Queueable;


    public function __construct(public readonly MarketplaceName $marketplace, public readonly string $message, public $gameId, public readonly int $stock, public bool $success = true)
    {

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public
    function via(object $notifiable): array
    {
        return ['broadcast', 'database'];
    }

    public
    function broadcastOn()
    {
        // return new Channel('requestResponse');
        return new Channel('notification');
    }

    public
    function toBroadcast($notifiable): BroadcastMessage
    {

        return new BroadcastMessage([
            'marketplace' => $this->marketplace->name,
            'message' => $this->message,
            'image' => MarketplaceName::defineImage($this->marketplace->value),
            'process' => NotificationTypeEnum::STOCK_NOTIFICATION->value,
            'success' => $this->success,
            'title' => 'Stock Notification',
            'date' => now()->diffForHumans()
        ]);
    }

    public
    function toDatabase(object $notifiable): array
    {
        return [
            'marketplace' => $this->marketplace->name,
            'message' => $this->message,
            'image' => MarketplaceName::defineImage($this->marketplace->value),
            'process' => NotificationTypeEnum::STOCK_NOTIFICATION->value,
            'game_id' => $this->gameId,
            'success' => $this->success,
            'title' => 'Stock Notification',
            'stock' => $this->stock,
            'date' => now()->diffForHumans()
        ];
    }
}
