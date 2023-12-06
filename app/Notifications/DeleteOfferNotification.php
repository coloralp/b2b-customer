<?php

namespace App\Notifications;

use App\Models\MarketPlace;
use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeleteOfferNotification extends Notification
{
    use Queueable;

    public MarketPlace $marketPlace;

    public bool $success;

    public string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($marketPlaceId, bool $success, $message)
    {
        $this->marketPlace = MarketPlace::findOrFail($marketPlaceId);
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
        return ['broadcast'];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {

        return new BroadcastMessage([
            'marketplace' => $this->marketPlace->name,
            'message' => $this->message,
            'success' => $this->success,
            'process' => 'ChangeOfferStatusJob',
            'date' => now()->diffForHumans()
        ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function broadcastOn()
    {
        // return new Channel('requestResponse');
        return new Channel('notification');
    }
}
