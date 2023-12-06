<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderExportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly array $payload, public readonly string $filename)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = (isset($this->payload['start']) and isset($this->payload['end']))
            ? $this->payload['start'] . '->' . $this->payload['end']
            : 'Order Export';


        return new Envelope(
            subject: $subject
        );
    }

    public function build()
    {
        return $this->markdown('mail.order-export-mail')
            ->attachFromStorageDisk('order_exports', $this->filename);
    }

}
