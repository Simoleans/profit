<?php

namespace App\Mail;

use App\Models\Header;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $clientEmail;
    public $clientName;
    public $status;
    /**
     * Create a new message instance.
     */
    public function __construct(Header $order, string $clientEmail, string $clientName,$status = null)
    {
        $this->order = $order;
        $this->clientEmail = $clientEmail;
        $this->clientName = $clientName;
        $this->status = $status;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {

        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-notification',
            with: [
                'order' => $this->order,
                'clientName' => $this->clientName,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    //switch para el subject
    public function getSubject()
    {
        switch ($this->status) {
            case 'A':
                return "Orden Aprobada #{$this->order->fact_num} - {$this->clientName}";
            case 'R':
                return "Orden Rechazada #{$this->order->fact_num} - {$this->clientName}";
            //anulada
            case 'AN':
                return "Orden Anulada #{$this->order->fact_num} - {$this->clientName}";
            default:
                return "Nueva Orden #{$this->order->fact_num} - {$this->clientName}";
        }
    }
}
