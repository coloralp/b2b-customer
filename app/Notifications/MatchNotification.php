<?php

namespace App\Notifications;

use App\Enums\MarketplaceName;
use App\Models\MarketPlace;
use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class MatchNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public MarketPlace $marketPlace;

    public bool $success;

    public string $message;

    public function __construct($marketplaceId, $success, $message, public $gameId)
    {
        $this->marketPlace = MarketPlace::findOrFail($marketplaceId);
        $this->success = $success;
        $this->message = $message;
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
            'message' => $this->message,
            'process' => 'Match',
            'success' => $this->success,
            'marketplace' => $this->marketPlace->name,
            'image' => MarketplaceName::defineImage($this->marketPlace->id),
            'title' => 'Match Notification',
            'date' => now()->diffForHumans()
        ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'process' => 'Match',
            'success' => $this->success,
            'marketplace' => $this->marketPlace->name,
            'image' => MarketplaceName::defineImage($this->marketPlace->id),
            'game_id' => $this->gameId,
            'title' => 'Match Notification',
            'date' => now()->diffForHumans()
        ];
    }
}
