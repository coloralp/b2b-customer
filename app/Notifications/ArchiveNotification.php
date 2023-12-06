<?php

namespace App\Notifications;

use App\Enums\MarketplaceName;
use App\Models\MarketPlace;
use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArchiveNotification extends Notification
{
    use Queueable;

    public function __construct($marketPlaceId, bool $success, $message, public $gameId)
    {
        $this->marketPlace = MarketPlace::findOrFail($marketPlaceId);
        $this->success = $success;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // return new Channel('requestResponse');
        return new Channel('notification');
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

    public function toBroadcast($notifiable): BroadcastMessage
    {

        return new BroadcastMessage([
            'marketplace' => $this->marketPlace->name,
            'message' => $this->message,
            'success' => $this->success,
            'process' => 'Archive',
            'image' => MarketplaceName::defineImage($this->marketPlace->id),
            'title' => 'Archive Process',
            'date' => now()->diffForHumans()
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'marketplace' => $this->marketPlace->name,
            'message' => $this->message,
            'success' => $this->success,
            'process' => 'Archive',
            'image' => MarketplaceName::defineImage($this->marketPlace->id),
            'game_id' => $this->gameId,
            'title' => 'Archive Process',
            'date' => now()->diffForHumans()
        ];
    }
}
