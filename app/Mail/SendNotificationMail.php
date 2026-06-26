<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $contactMessage;
    public ?string $mailSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->contactMessage = $data['message'] ?? '';
        $this->mailSubject = $data['subject'] ?? null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Send Notification Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.SendNotification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
