<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Attachment;

class OrderShipped extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly Order $order)
    {

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CDKeyci ' . $this->order->order_code,
        );
    }


    /**
     * Get the message content definition.
     */
    public function build()
    {

        $name = 'CDKeyci' . $this->order['order_code'] . '.zip';
        return $this->markdown('emails.orders.shipped')
            ->with([
                'data' => $this->order
            ])->attachFromStorageDisk('orderZip', $this->order['order_code'] . '.zip', $name)->subject("CDKeyci - Order Number:" . $this->order->order_code);
    }

}
