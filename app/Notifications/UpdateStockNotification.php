<?php

namespace App\Notifications;

use App\Enums\MarketplaceName;
use App\Models\MarketPlace;
use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UpdateStockNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public MarketPlace $marketPlace;

    public function __construct($marketPlaceId, public $message, public $gameId, public $success)
    {
        $this->marketPlace = MarketPlace::findOrFail($marketPlaceId);
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
            'marketplace' => $this->marketPlace->name,
            'message' => $this->message,
            'image' => MarketplaceName::defineImage($this->marketPlace->id),
            'process' => 'UpdateStock',
            'success' => $this->success,
            'title' => 'Update Stock Notification',
            'date' => now()->diffForHumans()
        ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'marketplace' => $this->marketPlace->name,
            'message' => $this->message,
            'image' => MarketplaceName::defineImage($this->marketPlace->id),
            'process' => 'UpdateStock',
            'game_id' => $this->gameId,
            'success' => $this->success,
            'title' => 'Update Stock Notification',
            'date' => now()->diffForHumans()

        ];
    }
}
