<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $data)
    {
        $this->data = collect($data);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Технічна допомога',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customer.support',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
